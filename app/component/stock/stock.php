<?php

namespace App\Component\Stock;

class stock
{

    static $data=[];


    /**
     * @param float $value
     * @param int   $n
     * @param int   $m
     * @param float $last
     * @return float
     */
    public static function SMA(float $value, int $n, int $m, float $last=50): float
    {
        return ($n - $m) / $n * $last + $m * $value / $n;
    }

    public static function EMA()
    {

    }

    public static function HHV(array $data)
    {

    }

    public static function LLV(array $data)
    {

    }
}