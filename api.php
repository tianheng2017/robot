<?php
use Workerman\Worker;
use Workerman\Timer;
use Workerman\Connection\TcpConnection;
use Workerman\Crontab\Crontab;

require_once 'vendor/autoload.php';
require_once 'config.php';

// 设置时区
date_default_timezone_set('PRC');

$redis = new Predis\Client($config['redis']);

Worker::$logFile = '/dev/null';

$ws_worker = new Worker("websocket://0.0.0.0:2000");

$ws_worker->count = 2;

// 在线人数
$connection_count = 0;

$ws_worker->onWorkerStart = function($connection) use ($redis)
{
    // 记录每日0点记录账户余额
    new Crontab('0 0 0 * * *', function() use ($redis) {
        
        echo '记录余额并清零今日做单次数'.PHP_EOL;
        
        // 记录日余额
        $redis->hset('statistical', 'today_eq', $redis->hget('statistical', 'eq'));
        
        // 日做单次数清零
        $redis->hset('statistical', 'today_count', 0);
        
    });
};

$ws_worker->onConnect = function($connection)
{
    // 有新的客户端连接时，在线人数+1
    global $connection_count;
    ++$connection_count;
};

$ws_worker->onClose = function($connection)
{
    // 客户端关闭时，连接数-1
    global $connection_count;
    $connection_count--;
};

$ws_worker->onMessage = function(TcpConnection $connection, $data) use ($redis)
{
    Timer::add(1, function() use ($connection, $redis) {
        // 在线人数
        global $connection_count;
        
        // 构建数据
        $res = json_encode([
            'code'  =>  0,
            'msg'   =>  '',
            'data'  =>  [
                // 运行日志
                'list'          =>  $redis->lrange('robotLog', 0, 100),
                // 其他数据
                'other'         =>  [
                    // USDT初始资金
                    'usdt_init'             =>  $redis->hget('config', 'usdt_init'),
                    // 止盈率
                    'profitRatio'           => $redis->hget('config', 'profitRatio'),
                    // 止损率
                    'stopLossRatio'         => $redis->hget('config', 'stopLossRatio'),
                    // 总补仓次数
                    'allAddPositionNum'     => $redis->hget('config', 'allAddPositionNum'),
                    // 首单ETH数量
                    'firstOrder'            => $redis->hget('config', 'firstOrder'),
                    // 账户总权益
                    'eq'                    =>  $redis->hget('statistical', 'eq'),
                    // 可用保证金
                    'availEq'               =>  $redis->hget('statistical', 'availEq'),
                    // 初始保证金
                    'frozenBal'             =>  $redis->hget('statistical', 'frozenBal'),
                    // 保证金率
                    'mgnRatio'              =>  $redis->hget('statistical', 'mgnRatio'),
                    // 实际杠杆
                    'notionalLever'         =>  $redis->hget('statistical', 'notionalLever'),
                    // 未实现盈亏
                    'upl'                   =>  $redis->hget('statistical', 'upl'),
                    // 总盈利
                    'total_profit'          =>  $redis->hget('statistical', 'total_profit'),
                    // 总盈利率
                    'total_profit_ratio'    =>  $redis->hget('statistical', 'total_profit_ratio'),
                    // 今日盈利
                    'today_profit'          =>  $redis->hget('statistical', 'today_profit'),
                    // 今日盈利率
                    'today_profit_ratio'    =>  $redis->hget('statistical', 'today_profit_ratio'),
                    // 当前补仓次数
                    'addPositionNum'        =>  $redis->get('addPositionNum') ?? 0,
                    // 开仓平均价
                    'avgPx'                 =>  $redis->hget('info', 'avgPx'),
                    // 最新成交价
                    'last'                  =>  $redis->hget('info', 'last'),
                    // 未实现收益
                    'upl'                   =>  $redis->hget('info', 'upl'),
                    // 未实现收益率
                    'uplRatio'              =>  $redis->hget('info', 'uplRatio'),
                    // 杠杆倍数
                    'lever'                 =>  $redis->hget('info', 'lever'),
                    // 今日做单次数
                    'today_count'           =>  $redis->hget('statistical', 'today_count'),
                    // 在线人数
                    'online'                =>  $connection_count,
                ],
            ],
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        
        //推送给客户端
        $connection->send($res);
    });
};

Worker::runAll();