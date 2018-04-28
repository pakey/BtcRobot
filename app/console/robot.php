<?php

namespace App\Console;

use App\Component\Exchange\Exchange;
use App\Component\stock;
use App\Model\Price;
use Kuxin\Console;


class Robot extends Console
{
    public function coin()
    {
        $exchange   = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        $api        = $exchange->huobi;
        $coin       = 'eos';
        $priceModel = Price::I();
        $priceModel->delete();
        $last     = $priceModel->where(['exchange' => $api->name, 'coin' => $coin, 'market' => $api->market])->order('id desc')->limit(1)->find();
        $lastTime = $last['time'] ?? 0;

        do {
            $nowTimestamp = time();
            $nowTime      = date('YmdHi', $nowTimestamp);
            $this->info(date('Y-m-d H:i:s'));
            if (!$lastTime) {
                $limit   = 100;
                $records = $api->getKline($coin, $limit);
                $priceModel->addInfos($records, $api->name, $coin, $api->market);
            } elseif ($lastTime != $nowTime) {
                $limit = floor((time() - strtotime($lastTime . '00')) % 86400 / 60);
                if (date('s', $nowTimestamp) > 0) {
                    $limit++;
                }
                $records = $api->getKline($coin, $limit);
                $priceModel->updateInfos($records, $api->name, $coin, $api->market);
            } else {
                $limit   = 1;
                $records = $api->getKline($coin, $limit);
                $priceModel->updateInfos($records, $api->name, $coin, $api->market);
            }
            $lastTime = date('YmdHi');
            sleep(1);
        } while (true);
    }

    public function test()
    {
        $time    = '201802091712';
        $oldData = Price::I()->where(['exchange' => 'huobi', 'coin' => 'eos', 'market' => 'usdt', 'time' => ['<', $time]])->order('id desc')->limit(30)->getField('time,num,open,close,k,d', true);

        $up = $down = 0;
        $n  = 0;
        var_dump(Price::I()->getLastSql());
        foreach ($oldData as $item) {
            $n++;
            if ($item['open'] < $item['close']) {
                $up += $item['close'] - $item['open'];
            } else {
                $down += $item['open'] - $item['close'];
            }
            if ($up == 0) {
                $val = 0;
            } elseif ($down == 0) {
                $val = 100;
            } else {
                $rs  = $up / $down;
                $val = round(100 - 100 / (1 + $rs), 4);
            }
            $this->info($n . ' ' . $item['time'] . '  ' . $val);
        }
    }

    public function rsi()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        // $api      = $exchange->binance;
        // $coin     = 'poe';
        $api     = $exchange->huobi;
        $coin    = 'eos';
        $oldData = Price::I()->where(['exchange' => 'huobi', 'coin' => 'eos', 'market' => 'usdt', 'time' => ['<=', 201802082304]])->order('id desc')->limit(30)->getField('time,num,open,close,k,d', true);
        $start   = array_shift($oldData);
        var_dump($start['time']);
        $n    = $up = $down = 0;
        $last = $start;
        foreach ($oldData as $item) {
            $n++;
            if ($last['close'] < $item['close']) {
                $down += $item['close'] - $last['close'];
            } else {
                $up += $last['close'] - $item['close'];
            }
            if ($up == 0) {
                $val = 0;
            } elseif ($down == 0) {
                $val = 100;
            } else {
                $rs = $up / $down;
                var_dump($up);
                $val = round(100 * $rs / (1 + $rs), 4);
            }
            $this->info($n . ' | ' . $item['time'] . '  | ' . $val);
            $last = $item;
        }
        exit;
        $oldData = $api->getKline($coin, '30', '30minute');
        krsort($oldData);
        $start = array_shift($oldData);
        $start = array_shift($oldData);
        var_dump($start['time']);
        $n    = $up = $down = 0;
        $last = $start;
        foreach ($oldData as $item) {
            $n++;
            if ($last['close'] < $item['close']) {
                $down += $item['close'] - $last['close'];
            } else {
                $up += $last['close'] - $item['close'];
            }
            if ($up == 0) {
                $val = 0;
            } elseif ($down == 0) {
                $val = 100;
            } else {
                $rs  = $up / $down;
                $val = round(100 * $rs / (1 + $rs), 4);
            }
            $this->info($n . ' | ' . $item['time'] . '  | ' . $val);
            $last = $item;
        }
    }

    public function testrsi()
    {
        $data = [13.15, 13.08, 13.03, 13.16, 13.23, 13.23, 13.1, 12.18, 12.07, 12.00, 12.02, 11.96, 12.05, 11.57, 11.65, 11.12, 11.06, 10.97, 10.92, 10.71, 10.24, 10.76, 10.76];
        $data = [12.18, 12.07, 12.00, 12.02, 11.96, 12.05, 11.57, 11.65, 11.12, 11.06, 10.97, 10.92, 10.71, 10.24, 10.76, 10.76];
        rsort($data);
        $last = 10.43;
        $n    = $up = $down = $upnum = $downnum = 0;
        foreach ($data as $item) {
            $n++;
            if ($last < $item) {
                $down += $item - $last;
                $downnum++;
            } elseif ($last > $item) {
                $up += $last - $item;
                $upnum++;
            }
            if ($up == 0) {
                $val = 0;
            } elseif ($down == 0) {
                $val = 100;
            } else {
                $upnum = $upnum + $downnum;
                $rs    = ($up / $upnum) / ($down / $upnum);
                $val   = round(100 * $rs / (1 + $rs), 4);
            }
            $this->info($n . '  | ' . $val);
            $last = $item;
        }
    }

    public function kd()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        // $api      = $exchange->binance;
        // $coin     = 'poe';
        $api     = $exchange->huobi;
        $coin    = 'btc';
        $oldData = $api->getKline($coin, '30', '1minute');
        $oldData = array_values($oldData);
        include KX_ROOT.'/func.php';
        // $kd=kd($oldData);
        // foreach($oldData as $k=>$v){
        //     $this->info( $v->time . '  | ' . $kd['lines']['0']['data'][$k] . ' | ' . $kd['lines']['1']['data'][$k] );
        // }
        //
        // $this->info('-------------');
        $kdj=kd(json_decode(json_encode($oldData)));
        // foreach($oldData as $k=>$v){
        //     $this->info( $v['time'] . '  | ' . $kdj['lines']['0']['data'][$k] . ' | ' . $kdj['lines']['1']['data'][$k]);
        // }
        $this->info('-------------');
        $last    = ['k' => '50', 'd' => '50'];
        $prersv  = 100;
        for ($i = 0; $i < count($oldData); $i++) {
            $length = array_slice($oldData, max(0,$i-9), min($i+1,9));
            $item   = array_shift(array_slice($length,-1));
            $low  = min(array_column($length, 'low'));
            $high = max(array_column($length, 'high'));
            if ($item['high'] == $item['low']) {
                $rsv = $prersv;
            } else {
                $rsv    = ($item['close'] - $low) / ($high - $low) * 100;
                $prersv = $rsv;
            }
            var_dump($rsv);
            $k = 2 / 3 * ($last['k'] ?? 50) + $rsv / 3;
            $d = 2 / 3 * ($last['d'] ?? 50) + $k / 3;
            $k = round($k, 4);
            $d = round($d, 4);
            // $this->info( $item['time'] .' | ' . $k . ' | ' . $d);
            $last = ['k' => $k, 'd' => $d];;
        }
        // $this->info('-------------');
        // for($i=0;$i<count($oldData);$i++){
        //     $data=array_slice($oldData,max(0,$i-9),min($i+1,9));
        //     $kd=Stock::KD($data);
        //     $oldData[$i+8]['k']=$kd['k'];
        //     $oldData[$i+8]['d']=$kd['d'];
        //     $this->info( $oldData[$i+8]['time'] . '  | ' . $kd['k'] . ' | ' . $kd['d'] );
        //     if($i>10){
        //         exit;
        //     }
        // }
    }

    public function huobi()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        $api      = $exchange->huobi;
        $record   = $api->getAccountStatus();
        var_dump($record);
    }
}