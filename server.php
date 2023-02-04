<?php

use Lin\Okex\OkexWebSocketV5;

require_once 'vendor/autoload.php';

$okex = new OkexWebSocketV5();

$okex->start();