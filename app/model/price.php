<?php

namespace App\Model;

use Kuxin\Model;

class Price extends Model
{
    protected $table = 'price';


    public function addInfos(array $records, string $exchange, string $coin, string $market): bool
    {
        if (empty($records)) {
            return true;
        }
        $data    = [];
        $oldData = $this->field('time,num,change,k,d')->where(['exchange' => $exchange, 'coin' => $coin, 'market' => $market, 'time' => ['<=', $records['0']['time']]])->order('id desc')->limit(12)->select();
        $oldData = array_column($oldData, null, 'time');
        foreach ($records as $time => $item) {
            if (isset($oldData['time'])) {
                $this->updateInfos([$item], $exchange, $coin, $market);
                $oldData = $this->field('time,num,change,k,d')->where(['exchange' => $exchange, 'coin' => $coin, 'market' => $market, 'time' => ['<=', $time]])->order('id desc')->limit(12)->select();
            } else {
                $changes = array_column($oldData, 'change');
                $kd      = $this->calcKd($item, $oldData['0'] ?? []);
                $data    = array_merge($item, [
                    'exchange' => $exchange,
                    'coin'     => $coin,
                    'market'   => $market,
                    'change'   => round(($item['close'] - $item['open']) / $item['open'] * 100, 4),
                    'rsi6'     => $this->calcRsi($changes, 6),
                    'rsi12'    => $this->calcRsi($changes, 12),
                    'k'        => $kd['k'],
                    'd'        => $kd['d'],
                ]);
                var_dump('new', $item);
                exit;
            }
        }
        return true;
    }

    public function updateInfos(array $records, string $exchange, string $coin, string $market): bool
    {

    }

    protected function calcRsi(array $data, int $num): float
    {
        $data=array_slice($data,0,$num);
        $up=$down=0;
        foreach($data as $value){
            if($value>0){
                $up+=$value;
            }else{
                $down+=$value;
            }
        }
        $rs=$up/$down;
        return round(100*$rs/(1+$rs),4);
    }

    protected function calcKd(array $data, array $last): array
    {
        $rsv = ($data['close'] - $data['low']) / ($data['high'] - $data['low']) * 100;
        $k   = 2 / 3 * ($last['k'] ?? 50) + $rsv / 3;
        $d   = 2 / 3 * ($last['d'] ?? 50) + $k / 3;
        $k   = round($k, 4);
        $d   = round($d, 4);
        return compact('k', 'd');
    }
}