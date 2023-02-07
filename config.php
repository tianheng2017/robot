<?php

$config = [
    'redis'=>[
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'database' => 0,
        'password' => 'a123654!@#',
    ],
    'keysecret' =>  [
        'key'       =>  '3ef36f20-8415-474c-903a-89c6bb6146ca',
        'secret'    =>  '8CC1B8AB14D551A8FCB43EEE955CA37A',
        'passphrase'=>  'a123654',
    ],
    'other' =>  [
        // 止盈率
        'profitRatio'               =>  30,
        // 止损率
        'stopLossRatio'             =>  100,
        // 止盈回调率（假设持续上涨，达到止盈条件也不止盈，而是等到主升浪拉升完毕，下跌一点时再止盈，让子弹多飞一会儿） - 待开发
        'profitCallbackRatio'       =>  0.5,
        // 补仓回调率（假设持续下跌，达到补仓点也不补仓，而是等到暴跌完毕，反弹一点再补仓，让均价更低） - 待开发
        'addPositionCallbackRatio'  =>  0.5,
        // 补仓策略，1倍投，2等比，默认倍投
        'tactics'                   =>  1,
        // 补仓次数
        'allAddPositionNum'         =>  10,
        // 首单数量
        'firstOrder'                =>  200,
        // USDT初始资金
        'usdtInit'                  =>  7000,
		// 交易对 默认ETH/USDT永续
		'currency'					=>	"ETH-USDT-SWAP",
    ],
];