<?php

namespace App\Model\Price;

use App\Component\Btc38;
use Kuxin\Loader;
use Kuxin\Model;

class Price extends Model
{

    public function createRecord($timestamp, $close = 0)
    {
        $data = $this->getInsertData($timestamp, $close);

        return $this->insert($data);
    }

    public function getInsertData($timestamp, $close = 0)
    {
        $api   = Loader::instance(Btc38::class);
        $total = $api->getInfoCount(substr($this->table, 6));
        $data  = [
            'usernum'   => $total['users'],
            'totalcoin' => $total['total'],
            'datetime'  => date('Y-m-d H:i:00', $timestamp),
            'day'       => date('Ymd', $timestamp),
            'week'      => date('YW', $timestamp),
            'month'     => date('Ym', $timestamp),
            'minute'    => intval($timestamp / 60),
            'minute5'   => intval($timestamp / 300),
            'minute10'  => intval($timestamp / 600),
            'minute15'  => intval($timestamp / 900),
            'minute30'  => intval($timestamp / 1800),
            'hour'      => date('YmdH', $timestamp),
            'open'      => $close,
            'close'     => $close,
            'high'      => $close,
            'lower'     => $close,
            'avg'       => $close,
        ];
        return $data;
    }


}