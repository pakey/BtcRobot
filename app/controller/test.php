<?php

namespace App\Controller;

use App\Component\Exchange\Exchange;
use App\Model\Price;
use Kuxin\Controller;

class Test extends Controller
{
    public function index()
    {
        echo '<pre>';
        $exchange = new Exchange(BINANCE_APIKEY, BINANCE_SECRET);
        $api      = $exchange->huobi;
        $res      = $api->getKline('btc', '5');
        var_dump($res);
    }
}