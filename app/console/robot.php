<?php
namespace App\Console;

use App\Component\Exchange\Exchange;
use App\Model\Price;
use Kuxin\Console;


class Robot extends Console{
    public function kline()
    {
        $exchange = new Exchange(BINANCE_APIKEY, BINANCE_SECRET);
        $binance  = $exchange->binance;
        $symbols=$binance->getSymbols('BTC');
        $priceModel=Price::I();
        foreach($symbols as $symbol){
            $last=$priceModel->where(['coin'=>$symbol['coin'],'market'=>$symbol['market']])->order('id desc')->find();
            $starTime=0;
            if($priceModel){
                $starTime=strtotime($last['time'].'00');
            }
            $res = $binance->getKline('poe','btc',500,'1m',$starTime);
            var_dump($res);exit;
        }
    }
}