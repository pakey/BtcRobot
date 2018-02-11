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
        $first   = current($records);
        $oldData = $this->where(['exchange' => $exchange, 'coin' => $coin, 'market' => $market, 'time' => ['<', $first['time']]])->order('id desc')->limit(24)->getField('time,num,rsi,close,k,d', true);
        foreach ($records as $time => $item) {
            if (isset($oldData[$time])) {
                $this->updateInfos([$item], $exchange, $coin, $market);
                $oldData = $this->where(['exchange' => $exchange, 'coin' => $coin, 'market' => $market, 'time' => ['<', $time]])->order('id desc')->limit(24)->getField('time,num,rsi,close,k,d', true);
            } else {
                $rsi  = $this->calcRsi(array_merge([$time=>$item],$oldData),  14);
                $kd   = $this->calcKd($oldData, $rsi);
                $item = array_merge($item, [
                    'exchange' => $exchange,
                    'coin'     => $coin,
                    'market'   => $market,
                    'change'   => round(($item['close'] - $item['open']) / $item['open'] * 100, 4),
                    'rsi'      => $rsi,
                    'k'        => $kd['k'],
                    'd'        => $kd['d'],
                ]);
                $res  = $this->insert($item);
                if (!$res) {
                    var_dump($this->getError());
                    var_dump($this->getLastSql());
                    exit;
                }
                $oldData = array_merge([$time => $item], $oldData);
                if (count($oldData) > 24) {
                    $oldData = array_slice($oldData, 0, 24);
                }
            }
        }
        exit;
        return true;
    }

    public function updateInfos(array $records, string $exchange, string $coin, string $market): bool
    {
        foreach ($records as $time => $item) {
            $oldData = $this->where(['exchange' => $exchange, 'coin' => $coin, 'market' => $market, 'time' => ['<=', $time]])->order('id desc')->limit(24)->getField('time,id,num,open,close,k,d', true);
            if (isset($oldData[$time])) {
                if ($oldData[$time]['num'] != $item['num']) {
                    //更新
                    $rsi  = $this->calcRsi(array_Merge([$time=>$item],$oldData),  14);
                    $kd   = $this->calcKd($item, $rsi);
                    $item = array_merge($item, [
                        'exchange' => $exchange,
                        'coin'     => $coin,
                        'market'   => $market,
                        'change'   => round(($item['close'] - $item['open']) / $item['open'] * 100, 4),
                        'rsi'      => $rsi,
                        'k'        => $kd['k'],
                        'd'        => $kd['d'],
                    ]);
                    $this->where(['id' => $oldData[$time]['id']])->update($item);
                }
                unset($records[$time]);
            } else {
                //新增
                $this->addInfos($records, $exchange, $coin, $market);
            }
        }
        return true;
        exit;
    }

    protected function calcRsi(array $data, int $num): float
    {
        if (empty($data)) {
            return 0;
        }
        $last=array_shift($data);
        $data = array_slice($data, 0, $num);
        $up   = $down = 0;
        foreach ($data as $item) {
            if ($last['close'] < $item['close']) {
                $down += $item['close'] - $last['close'];
            } else {
                $up += $last['close'] - $item['close'];
            }
            $last = $item;
        }
        if ($up == 0) {
            $val = 0;
        } elseif ($down == 0) {
            $val = 100;
        } else {
            $rs  = $up / $down;
            $val = round(100 * $rs / (1 + $rs), 4);
        }
        return $val;
    }

    protected function calcKd(array $data, int $rsi): array
    {
        if(empty($data)){
            return ['k'=>50,'d'=>50];
        }
        $last=array_shift(array_slice($data,0,1));
        $data=array_slice($data,0,10);
        $closes=array_column($data,'close');
        $min=min($closes);
        $max=max($closes);
        if ($min == $max) {
            $rsv = 100;
        } else {
            $rsv = ($last['close'] -$min) / ($max - $min) * 100;
        }
        // $rsis=array_column(array_slice($data,0,14),'rsi');
        // $min=min($rsis);
        // $max=max($rsis);
        // if ($min == $max) {
        //     $rsv = 100;
        // } else {
        //     $rsv = ($rsi -$min) / ($max - $min) * 100;
        // }
        var_dump($rsv,$last['close'] ,$min);
        $k = 2 / 3 * ($last['k'] ?? 50) + $rsv / 3;
        $d = 2 / 3 * ($last['d'] ?? 50) + $k / 3;
        $k = round($k, 4);
        $d = round($d, 4);
        return compact('k', 'd');
    }
}