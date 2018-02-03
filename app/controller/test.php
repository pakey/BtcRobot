<?php

namespace App\Controller;

use App\Component\Exchange\Exchange;
use Kuxin\Controller;

class Test extends Controller
{
    public function index()
    {
        echo '<pre>';
        $exchange = new Exchange(BINANCE_APIKEY, BINANCE_SECRET);
        $binance  = $exchange->binance;

        $res = $binance->getKline('poe','btc','5');
        var_dump($res);
    }
}