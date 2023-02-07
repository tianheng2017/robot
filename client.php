<?php

use Lin\Okex\OkexV5;
use Lin\Okex\OkexWebSocketV5;

require_once 'vendor/autoload.php';
require_once 'config.php';

/**
 *  OKEX模拟盘简易单用户单币种量化机器人
**/
class Client
{
    public $okex;
    public $okex_socket;
    public $config;
    public $redis;
    
    public function __construct(array $config)
    {
        // 初始化/载入配置
        $this->init($config);

        // 频道订阅
        $this->okex_socket->subscribe([
            // 持仓频道
            ["channel" => "positions", "instType" => "SWAP", "instId" => $this->redis->hget('config', 'currency')],
        ]);
        
        // 获取持仓频道数据推送
        $this->okex_socket->getSubscribe([
            ["channel" => "positions", "instType" => "SWAP", "instId" => $this->redis->hget('config', 'currency')],
        ], function ($data) {
            // 机器人没有暂停 且 接口也通畅
            if (!$this->redis->exists('stop_robot') && !$this->redis->exists('rest')) {
                // 当前有仓位
                if (!empty(array_values($data)[0]['data'])) {
                    // 持续刷新持仓锁，连续30秒都没持仓数据才准开单，只做单仓位
                    $this->redis->setex('positionLock', 30, 1);
                    // 解析数据
                    $data = array_values($data)[0]['data'];
                    // 持仓处理
                    $this->orderHandle($data);
                    // 当前无持仓，也无开单锁
                } else if (!$this->redis->exists('positionLock') && !$this->redis->exists('orderLock')) {
                    $this->writeln('近30秒无持仓，随机方向开单...');
                    // 上开单锁，不重要，处理完无需解锁，休息下
                    $this->redis->setex('orderLock', 10, 1);
                    // 随机多空方向，随机0-100，大于50做多，否则做空
                    mt_rand(0, 100) > 50 ? $this->createOrder('buy', 'long') : $this->createOrder('sell', 'short');
                }
            }
        }, true);
    }
    
    // 初始化
    public function init(array $config)
    {
        $this->config = $config;
        $this->redis = new Predis\Client($this->config['redis']);
        
        $this->okex = new OkexV5($this->config['keysecret']['key'], $this->config['keysecret']['secret'], $this->config['keysecret']['passphrase']);
        // 使用模拟盘下单
        $this->okex->setOptions([
            'headers' => ['x-simulated-trading' => 1]
        ]);
        
        $this->okex_socket = new OkexWebSocketV5();
        // 传入API信息
        $this->okex_socket->keysecret($this->config['keysecret']);
        
        // 将机器人配置载入redis，方便动态配置
        // USDT初始资金
        $this->redis->hset('config', 'usdt_init', $this->config['other']['usdtInit']);
        // 止盈率
        $this->redis->hset('config', 'profitRatio', $this->config['other']['profitRatio']);
        // 止损率
        $this->redis->hset('config', 'stopLossRatio', $this->config['other']['stopLossRatio']);
        // 总补仓次数
        $this->redis->hset('config', 'allAddPositionNum', $this->config['other']['allAddPositionNum']);
        // 首单数量
        $this->redis->hset('config', 'firstOrder', $this->config['other']['firstOrder']);
        // 补仓策略
        $this->redis->hset('config', 'tactics', $this->config['other']['tactics']);
		// 交易对
        $this->redis->hset('config', 'currency', $this->config['other']['currency']);
		// 交易币种
		$this->redis->hset('config', 'currencyCoin', explode('-', $this->config['other']['currency'])[0] ?? 'ETH');
    }
    
    // 持仓处理
    public function orderHandle(array $data)
    {
        foreach ($data as $k => $v) {
            // 简单校验，保证数据有效
            if (is_numeric($v['upl'])) {
                // 记录当前仓位基本信息
                $this->recordInfo($v);
                // 未实现USDT收益
                $v['upl'] = round($v['upl'], 2);
                // 未实现收益率
                $v['uplRatio'] = round($v['uplRatio'] * 100, 2);
                // $this->writeln('本单盈利：'.$v['upl'].' USDT，盈利率：'.$v['uplRatio'].' %');
                
                // 止盈条件
                $condition1 = $v['uplRatio'] >= $this->redis->hget('config', 'profitRatio');
                // 止损条件
                $condition2 = $v['uplRatio'] < 0 && ((abs($v['uplRatio']) >= $this->redis->hget('config', 'stopLossRatio')));
                
                // 达到清仓条件（止盈/止损），且没有清仓锁
                if (($condition1 || $condition2) && !$this->redis->exists('closeLock')) {
                    // 如果此刻准备止损清仓了，且倍投次数未超限，先拦截进行补仓
                    if ($condition2 && ((int)$this->redis->get('addPositionNum') < $this->redis->hget('config', 'allAddPositionNum'))) {
                        // （待开发）补仓回调逻辑：假设持续下跌，达到补仓点也不补仓，而是等到暴跌完毕反弹一点再补仓，让均价更低！
                        //.............
                        
                        // 遇到补仓锁，跳过不处理
                        if ($this->redis->exists('addPositionLock')) {
                            $this->writeln('有正在补仓的操作，本轮跳过...');
                            continue;
                        }
                        $this->writeln('达到补仓条件，开始补仓...');
                        // 上补仓锁，该位置的价格较重要，补仓完毕需立即解锁
                        $this->redis->setex('addPositionLock', 10, 1);
                        // 根据补仓策略计算补仓数量
                        $amount = $this->tacticsCalc($v['availPos']);
                        // 保持持仓方向补仓
                        $v['posSide'] == 'long' ? $this->createOrder('buy', 'long', $amount) : $this->createOrder('sell', 'short', $amount);
                        // 拦截完毕，处理下一条数据
                        continue;
                        
                    }
                    
                    // （待开发）止盈回调逻辑：假设持续上涨，达到止盈条件也不止盈，而是等到主升浪拉升完毕，下跌一点时再止盈，让子弹多飞一会儿！
                    //..............
    
                    $this->writeln('达到'.($condition1 ? '止盈' : '止损').'条件，开始清仓...');
                    // 上清仓锁，该位置的价格较重要，清仓完毕需立即解锁
                    $this->redis->setex('closeLock', 10, 1);
                    // 清仓处理
                    $this->closePosition($v['posSide']);
                }
            }
        }
    }
    
    // 开单/补仓
    public function createOrder(string $side = 'buy', string $posSide = 'long', int $sz = 0)
    {
        // 随机开单才提示
        ($sz == 0) && ($this->writeln(($side == 'buy' && $posSide == 'long') ? '方向确定，开多...' : '方向确定，开空...'));
        
        try {
			// 此处使用API下单，而不是websocket，保证稳定性
            $result = $this->okex->trade()->postOrder([
                'instId'    =>  $this->redis->hget('config', 'currency'),
                'tdMode'    =>  'cross',
                'side'      =>  $side,
                'posSide'   =>  $posSide,
                'ordType'   =>  'market',
                // 非补仓读取首单数量
                'sz'        =>  $sz ?: $this->redis->hget('config', 'firstOrder'),
            ]);
            
            // code强转整数
            $result['code'] = (int)$result['code'];
            // 如果下单时接口不通
            if (in_array($result['code'], [50001, 50004, 50013, 50026])) {
                $this->writeln('接口不通，休息60秒...');
                $this->redis->setex('rest', 60, 1);
                return false;
            } else if ($sz > 0 && $result['code'] == 51004) { // 如果接口通畅，但补仓时余额不足
                $this->writeln('余额不足，补仓失败，准备清仓...');
                // 增大补仓次数，让下次请求触发清仓
                $this->redis->set('addPositionNum', 9999999999);
                return false;
            // 其他错误
            } else if ($result['code'] > 0) {
                throw new Exception(json_encode($result['data'][0]['sMsg']));
            }
            
            // 今日做单次数+1
            $this->redis->hincrby('statistical', 'today_count', 1);
            // 记录本次下单/补仓数量
            $this->redis->set('lastNum', $sz ?: $this->redis->hget('config', 'firstOrder'));
            // 补仓成功
            if ($sz > 0) {
                $this->writeln('补仓成功，数量：'.$sz.' '.$this->redis->hget('config', 'currencyCoin'));
                // 补仓次数+1
                $this->redis->Incr('addPositionNum');
                // 解补仓锁
                $this->redis->del('addPositionLock');
                return true;
            }
            // 开单成功
            $this->writeln('开单成功，数量：'.$this->redis->hget('config', 'firstOrder').' '.$this->redis->hget('config', 'currencyCoin'));
        } catch (\Exception $e){
            $this->writeln('报错：'.$e->getMessage());
        }
        return true;
    }
    
    // 市价清仓
    public function closePosition($posSide)
    {
        try {
            $result = $this->okex->trade()->postClosePosition([
                'instId'    =>  $this->redis->hget('config', 'currency'),
                'posSide'   =>  $posSide,
                'mgnMode'   =>  'cross',
            ]);
            
            // code转整数
            $result['code'] = (int)$result['code'];
            // 如果下单时接口不通
            if (in_array($result['code'], [50001, 50004, 50013, 50026])) {
                $this->writeln('接口不通，休息60秒...');
                $this->redis->setex('rest', 60, 1);
                return false;
            // 普通错误    
            } else if ($result['code'] > 0) {
                throw new Exception(json_encode($result['data'][0]['sMsg']));
            }
            
            $this->writeln('清仓成功，等待开单...');
            // 解清仓锁
            $this->redis->del('closeLock');
            // 解补仓锁
            $this->redis->del('addPositionLock');
            // 补仓次数清零
            $this->redis->del('addPositionNum');
            // 上次下单数量清零
            $this->redis->del('lastNum');
        } catch (\Exception $e){
            $this->writeln('报错：'.$e->getMessage());
        }
        return true;
    }
    
    // 日志操作与显示
    public function writeln(string $msg)
    {
        // 显示换行
        $msg = date('H:i:s', time()).' -> '.$msg.PHP_EOL;
        // 输出到cli
        echo $msg;
        // 消息入队列
        $this->redis->lpush('robotLog', $msg);
        // 队列只保存最新13条消息
        if ($this->redis->llen('robotLog') > 13) {
            $this->redis->rpop('robotLog');
        }
        return true;
    }
    
    // 通过策略计算补仓数量
    public function tacticsCalc($value)
    {
        // 获取补仓策略类型
        $tactics = $this->redis->hget('config', 'tactics');
        // 首单数量
        $firstNum = $this->redis->hget('config', 'firstOrder');
        // 获取上次补仓数量
        $lastNum = (float)$this->redis->get('lastNum');
        if ($lastNum <= 0) {
            $lastNum = $firstNum;
        }
        
        switch ($tactics) {
            // 倍投
            case 1:
                $amount = $lastNum * 2;
                break;
            // 等比
            case 2:
                $amount = $lastNum;
                break;
            // 默认倍投
            default:
                $amount = $lastNum * 2;
        }
        
        return $amount;
    }
    
    // 记录当前仓位基本信息
    public function recordInfo(array $data)
    {
        if (empty($data)) return false;
        
        // 开仓平均价
        $this->redis->hset('info', 'avgPx', round($data['avgPx'], 4));
        // 最新成交价
        $this->redis->hset('info', 'last', round($data['last'], 4));
        // 未实现收益
        $this->redis->hset('info', 'upl', round($data['upl'], 2));
        // 未实现收益率
        $this->redis->hset('info', 'uplRatio', round($data['uplRatio'] * 100, 2));
        // 杠杆倍数
        $this->redis->hset('info', 'lever', $data['lever']);
        // 可用保证金
        $this->redis->hset('info', 'lever', $data['lever']);
    }
	
	// 动态止盈率调整
	public function dynamicProfit()
	{
	    
	}
    
    // 动态止损率调整
    public function dynamicStopLoss()
    {
        
    }
}

new Client($config);