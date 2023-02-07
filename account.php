<?php

use Lin\Okex\OkexV5;
use Lin\Okex\OkexWebSocketV5;

require_once 'vendor/autoload.php';
require_once 'config.php';

/**
 *  账户信息处理
**/
class Account
{
    public $okex;
    public $okex_socket;
    public $config;
    public $redis;
    
    public function __construct(array $config)
    {
        // 初始化
        $this->init($config);
        
        // 频道订阅
        $this->okex_socket->subscribe([
            // 账户频道
            ["channel" => "account", "ccy" => "USDT"],
            
        ]);
        
        // 获取账户频道数据推送
        $this->okex_socket->getSubscribe([
            // 账户频道
            ["channel" => "account", "ccy" => "USDT"],
            
        ], function ($data) {
            
            // 解析数据
            if (!empty(array_values($data)[0]['data'][0]['details'][0])) {
                // 解析数据
                $data = array_values($data)[0]['data'][0]['details'][0];
                
                // 账户USDT总权益
                $this->redis->hset('statistical', 'eq', round($data['eq'], 2));
                // 当前可用保证金
                $this->redis->hset('statistical', 'availEq', round($data['availEq'], 2));
                // 本单初始保证金
                $this->redis->hset('statistical', 'frozenBal', round($data['frozenBal'], 2));
                // 本单保证金率
                $this->redis->hset('statistical', 'mgnRatio', bcmul($data['mgnRatio'], 100, 2));
                // 本单实际杠杆
                $this->redis->hset('statistical', 'notionalLever', round($data['notionalLever'], 2));
                // 本单未实现盈亏
                $this->redis->hset('statistical', 'upl', round($data['upl'], 2));
                // 历史盈利
                $this->redis->hset('statistical', 'total_profit', round($data['eq'] - $this->redis->hget('config', 'usdt_init'), 2));
                // 历史盈利率
                $this->redis->hset('statistical', 'total_profit_ratio', round((($data['eq'] - $this->redis->hget('config', 'usdt_init')) / $this->redis->hget('config', 'usdt_init')) * 100, 4));
                // 今日盈利
                $this->redis->hset('statistical', 'today_profit', round($data['eq'] - $this->redis->hget('statistical', 'today_eq'), 2));
                // 今日盈利率
                $this->redis->hset('statistical', 'today_profit_ratio', $this->redis->hget('statistical', 'today_eq') ? round((($data['eq'] - $this->redis->hget('statistical', 'today_eq')) / $this->redis->hget('statistical', 'today_eq')) * 100, 4) : 0);
            }
            
        }, true);
    }
    
    public function init(array $config)
    {
        $this->config = $config;
        $this->redis = new Predis\Client($this->config['redis']);
        
        $this->okex = new OkexV5($this->config['keysecret']['key'], $this->config['keysecret']['secret'], $this->config['keysecret']['passphrase']);
        // 使用模拟盘下单
        $this->okex->setOptions([
            'headers'=>['x-simulated-trading' => 1]
        ]);
        
        $this->okex_socket = new OkexWebSocketV5();
        
        // 传入API信息
        $this->okex_socket->keysecret($this->config['keysecret']);
        
        // 将机器人配置载入redis，方便动态配置（第二种刷新配置方式，第一种是重启client，但每次去重启client影响炒币，所以重启account即可）
        // USDT初始资金
        $this->redis->hset('config', 'usdt_init', $this->config['other']['usdtInit']);
        // 止盈率
        $this->redis->hset('config', 'profitRatio', $this->config['other']['profitRatio']);
        // 止损率
        $this->redis->hset('config', 'stopLossRatio', $this->config['other']['stopLossRatio']);
        // 总补仓次数
        $this->redis->hset('config', 'allAddPositionNum', $this->config['other']['allAddPositionNum']);
        // 首单ETH数量
        $this->redis->hset('config', 'firstOrder', $this->config['other']['firstOrder']);
        // 补仓策略
        $this->redis->hset('config', 'tactics', $this->config['other']['tactics']);
		// 交易对
		$this->redis->hset('config', 'currency', $this->config['other']['currency']);
		// 交易币种
		$this->redis->hset('config', 'currencyCoin', explode('-', $this->config['other']['currency'])[0] ?? 'ETH');
    }
}

new Account($config);
