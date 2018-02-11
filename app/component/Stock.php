<?php

namespace App\Component;

class Stock
{
    public static function KD(array $data, int $n = 9, int $m1 = 3, int $m2 = 3): array
    {
        if (empty($data)) {
            return ['k' => 0, 'd' => 0];
        }
        $data    = array_slice($data, 0, $n);
        krsort($data);
        $current = array_shift(array_slice($data, 0, 1));
        $last    = array_shift(array_slice($data, 1, 1));
        $closes  = array_column($data, 'close');
        $min     = min($closes);
        $max     = max($closes);
        if ($min == $max) {
            $rsv = 100;
        } else {
            $rsv = ($current['close'] - $min) / ($max - $min) * 100;
        }
        $k = self::SMA($rsv, $m1, 1, (float)$last['k']);
        $d = self::SMA($k, $m2, 1, (float)$last['k']);
        $k = round($k, 4);
        $d = round($d, 4);
        return compact('k', 'd');
    }

    public static function KDJ()
    {

    }

    public static function RSI()
    {

    }

    public static function SMA(float $value, int $n, int $m, float $last): float
    {
        return ($n - $m) / $n * ($last ?? 50) + $m * $value / $n;
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