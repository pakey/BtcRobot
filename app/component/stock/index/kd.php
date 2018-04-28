<?php

namespace App\Component\Stock\Index;

use App\Component\Stock\Index;
use App\Component\Stock\Tool;

class KD extends Index
{

    protected function call(array $data, int $n = 9, int $m1 = 3, int $m2 = 3): array
    {
        if (empty($data)) {
            return ['k' => 0, 'd' => 0];
        }
        $data = array_slice($data, 0, $n);
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
        $k = Tool::SMA($rsv, $m1, 1, (float)$last['k']);
        $d = Tool::SMA($k, $m2, 1, (float)$last['k']);
        $k = round($k, 4);
        $d = round($d, 4);
        return compact('k', 'd');
    }
}