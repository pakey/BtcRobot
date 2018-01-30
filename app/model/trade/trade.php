<?php

namespace App\Model\Trade;

use Kuxin\Model;

class Trade extends Model
{

    public function calcFee($coin, $money, $num = 6)
    {
        return round($money * (in_array($coin, ['tmc', 'btc', 'ltc']) ? 0.002 : 0.001), $num);
    }

    public function getPrice($coin)
    {
        return $this->table('trade_' . $coin)->order('id desc')->getField('price');
    }
}