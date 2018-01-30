<?php

namespace App\Model\Tmc;

use App\Component\Btc38;
use Kuxin\Loader;
use Kuxin\Model;

class Price extends Model
{
    protected $table = 'tmc_price';


    public function createRecord($timestamp)
    {
        $api   = Loader::instance(Btc38::class);
        $total = $api->getInfoCount('tmc');
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
        ];

        return $this->insert($data);
    }


}