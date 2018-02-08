<?php
namespace App\Console;

use App\Component\Exchange\Exchange;
use App\Model\Price;
use Kuxin\Console;


class Robot extends Console{
    public function coin()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        $api  = $exchange->huobi;
        $coin='eos';
        $priceModel=Price::I();
        $last=$priceModel->where(['exchange'=>$api->name,'coin'=>$coin,'market'=>$api->market])->order('id desc')->limit(1)->find();
        $lastTime=$last['time']??0;

        do{
            if(!$lastTime){
                $limit=10000;
                $records=$api->getKline($coin,$limit);
                $priceModel->addInfos($records,$api->name,$coin,$api->market);
            }elseif($lastTime!=date('YmdHi')){
                $limit = strtotime($lastTime.'00')-time()+1;
                $records=$api->getKline($coin,$limit);
                $priceModel->updateInfos(array_shift($records),$api->name,$coin,$api->market);
            }else{
                $limit=1;
                $records=$api->getKline($coin,$limit);
                $priceModel->updateInfos($records,$api->name,$coin,$api->market);
            }
            $lastTime=date('YmdHi');
            sleep(1);
            $this->info(date('Y-m-d H:i:s'));
            exit;
        }while(true);
    }
}