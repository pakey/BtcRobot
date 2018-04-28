<?php

namespace App\Component\Stock;

class Tool
{
    /**
     * @param float $value
     * @param int   $n
     * @param int   $m
     * @param float $last
     * @return float
     */
    public static function SMA(float $value, int $n, int $m, float $last = 50): float
    {

        return ($n - $m) / $n * $last + $m * $value / $n;
    }
}