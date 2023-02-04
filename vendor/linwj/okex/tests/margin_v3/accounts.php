<?php


/**
 * @author lin <465382251@qq.com>
 *
 * Fill in your key and secret and pass can be directly run
 *
 * Most of them are unfinished and need your help
 * https://github.com/zhouaini528/okex-php.git
 * */
use Lin\Okex\OkexMargin;

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';

$okex=new OkexMargin($key,$secret,$passphrase);

//You can set special needs
$okex->setOptions([
    //Set the request timeout to 60 seconds by default
    'timeout'=>10,

    //If you are developing locally and need an agent, you can set this
    //'proxy'=>true,
    //More flexible Settings
    /* 'proxy'=>[
     'http'  => 'http://127.0.0.1:12333',
     'https' => 'http://127.0.0.1:12333',
     'no'    =>  ['.cn']
     ], */
    //Close the certificate
    //'verify'=>false,
]);

//This endpoint supports getting the list of assets(only show pairs with balance larger than 0), the balances, amount available/on hold in spot_v3 accounts.
try {
    $result=$okex->account()->getAll();
    print_r($result);
}catch (\Exception $e){
    print_r(json_decode($e->getMessage(),true));
}

//This endpoint supports getting the balance, amount available/on hold of a token in spot_v3 account_v3.
try {
    $result=$okex->account()->get([
        'instrument_id'=>'BTC-USDT'
    ]);
    print_r($result);
}catch (\Exception $e){
    print_r(json_decode($e->getMessage(),true));
}

//All paginated requests return the latest information (newest) as the first page sorted by newest (in chronological time) first.
try {
    $result=$okex->account()->getLedger([
        'instrument_id'=>'BTC-USDT',
        'currency'=>'BTC',
        'limit'=>2,
    ]);
    print_r($result);
}catch (\Exception $e){
    print_r(json_decode($e->getMessage(),true));
}
