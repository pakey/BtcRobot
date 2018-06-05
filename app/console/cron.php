<?php

namespace App\Console;

use App\Component\Exchange\Exchange;
use Kuxin\Console;
use Swoole\Process;

class Cron extends Console
{

    protected $all = 0;

    protected $worker = [];

    public function user()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        $api=$exchange->huobi;
        $infos=$api->getAccountStatus();
        foreach($infos['data'] as $v){
            if($v['type']=='spot'){
                var_dump($v['id']);
            }
        }
        return '错误';
    }

    public function pool()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        // $api      = $exchange->binance;
        // $coin     = 'poe';
        $day       = 7;
        $api       = $exchange->huobi;
        $coins     = ['eos'];
        $coins     = $api->getSymbols();
        $this->all = 0;
        $pool      = new \Swoole\Process\Pool(count($coins));

        $pool->on("WorkerStart", function ($pool, $workerId) use ($coins, $api, $day) {
            $coin = $coins[$workerId];
            do {
                $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . '初始化数据获取......');
                $oldData = $api->getKline($coin, '1000', '1minute');
            } while (count($oldData) > 10);
            $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . '初始化数据获取完毕');
            $last   = ['k' => '50', 'd' => '50', 'close' => 0];
            $prersv = 100;
            $data   = [];
            for ($i = 0; $i <= count($oldData); $i++) {
                $length = array_slice($oldData, max(0, $i - $day), min($i + 1, $day));
                {
                    // 计算kd
                    $item = array_shift(array_slice($length, -1));
                    $low  = min(array_column($length, 'low'));
                    $high = max(array_column($length, 'high'));
                    if ($item['high'] == $item['low']) {
                        $rsv = $prersv;
                    } else {
                        $rsv    = ($item['close'] - $low) / ($high - $low) * 100;
                        $prersv = $rsv;
                    }
                    $k = 2 / 3 * ($last['k'] ?? 50) + $rsv / 3;
                    $d = 2 / 3 * ($last['d'] ?? 50) + $k / 3;
                    $k = round($k, 4);
                    $d = round($d, 4);
                }
                {
                    //计算rsi
                    $up = $down = $upnum = $downnum = 0;
                    foreach ($length as $key => $v) {
                        if ($key > 0) {
                            $lastclose = $length[$key - 1]['close'];
                            if ($lastclose < $v['close']) {
                                $down += $v['close'] - $lastclose;
                                $downnum++;
                            } elseif ($lastclose > $v['close']) {
                                $up += $lastclose - $v['close'];
                                $upnum++;
                            }
                        }
                    }
                    if ($up == 0) {
                        $rsi = 0;
                    } elseif ($down == 0) {
                        $rsi = 100;
                    } else {
                        $rs  = $up / $down;
                        $rsi = round(100 * $rs / (1 + $rs), 4);
                    }
                }
                // $this->info($item['time'] . ' | ' . $k . ' | ' . $d);
                $data[$item['time']] = ['time' => $item['time'], 'k' => $k, 'd' => $d, 'price' => $item['price'], 'rsv' => $rsv, 'rsi' => $rsi];
                if (count($data) > 10) {
                    $first = array_shift(array_slice($data, 0, 1));
                    unset($data[$first['time']]);
                }
                $last = ['k' => $k, 'd' => $d, 'close' => $item['close']];;
            }

            $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . '初始值计算完毕');
            $selltime = $buy = $lirun = 0;
            do {
                $lastData = array_pop($api->getKline($coin, '1', '1minute'));
                if (empty($lastData['time']) || empty($lastData['price'])) {
                    sleep(1);
                    continue;
                }
                if (isset($oldData[$lastData['time']])) {
                    $last                       = array_shift(array_slice($data, -2, 1));
                    $oldData[$lastData['time']] = $lastData;
                } else {
                    $last                       = array_shift(array_slice($data, -1, 1));
                    $oldData[$lastData['time']] = $lastData;
                    {
                        $v        = $last;
                        $k        = count($data) - 1;
                        $calcData = array_values($data);

                        if ($v['price'] > $calcData[$k - 1]['price'] && $v['k'] > $v['d'] && $v['k'] <= 40 && $v['k'] >= 5 && ($calcData[$k - 1]['k'] <= $calcData[$k - 1]['d'] && $calcData[$k - 2]['k'] <= $calcData[$k - 2]['d'])) {
                            if (!$buy) {
                                $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 买进', 'success');
                                $buy = $v['price'];
                            }
                        } elseif ($v['k'] < $v['d'] && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] && $calcData[$k - 2]['k'] >= $calcData[$k - 2]['d']))) {
                            if ($buy) {
                                $zhege     = round(($v['price'] - $buy) / $buy, 8);
                                $lirun     += $zhege;
                                $this->all += $zhege;
                                $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 卖出 | ' . $zhege . ' | ' . $lirun . ' | ' . $this->all, 'warning');
                                $buy = 0;
                            }
                        } else {
                            $selltime = 0;
                            // $this->info(date('Y-m-d H:i:s') . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] );
                        }
                    }
                }
                $length = array_slice($oldData, -$day);
                {
                    $low  = min(array_column($length, 'low'));
                    $high = max(array_column($length, 'high'));
                    if ($lastData['high'] == $lastData['low']) {
                        $rsv = $prersv;
                    } else {
                        $rsv    = ($lastData['close'] - $low) / ($high - $low) * 100;
                        $prersv = $rsv;
                    }
                    $k = 2 / 3 * ($last['k'] ?? 50) + $rsv / 3;
                    $d = 2 / 3 * ($last['d'] ?? 50) + $k / 3;
                    $k = round($k, 4);
                    $d = round($d, 4);
                }
                $data[$lastData['time']] = ['time' => $lastData['time'], 'k' => $k, 'd' => $d, 'price' => $lastData['price'], 'rsv' => $rsv];
                if (count($data) > 10) {
                    $first = array_shift(array_slice($data, 0, 1));
                    unset($data[$first['time']]);
                }
                {
                    $v        = $data[$lastData['time']];
                    $k        = count($data) - 1;
                    $calcData = array_values($data);

                    if ($v['k'] > $v['d'] && $v['k'] <= 40 && $v['k'] >= 5 && ($calcData[$k - 1]['k'] <= $calcData[$k - 1]['d'] && $calcData[$k - 2]['k'] <= $calcData[$k - 2]['d'] && $calcData[$k - 3]['k'] <= $calcData[$k - 3]['d'])) {
                        if (!$buy) {
                            $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 买进', 'success');
                            $buy = $v['price'];
                        }
                    } elseif ($v['k'] < $v['d'] && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] && $calcData[$k - 2]['k'] >= $calcData[$k - 2]['d'] && $calcData[$k - 3]['k'] >= $calcData[$k - 3]['d']))) {
                        if ($buy) {
                            if ($selltime >= 3) {
                                $zhege     = round(($v['price'] - $buy) / $buy, 8);
                                $lirun     += $zhege;
                                $this->all += $zhege;
                                $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 卖出 | ' . $zhege . ' | ' . $lirun . ' | ' . $this->all, 'warning');
                                $buy = 0;
                            } elseif ($selltime) {
                                $selltime++;
                                // $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | selltime ' . $selltime, 'info');
                            } else {
                                $selltime = 1;
                                // $this->info(date('Y-m-d H:i:s') . " [{$coin}] " . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | selltime ' . $selltime, 'info');
                            }
                        }
                    } else {
                        $selltime = 0;
                        // $this->info($v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . $v['rsi']);
                    }
                }
                sleep(1);

            } while (true);
        });

        $pool->on("WorkerStop", function ($pool, $workerId) {
            echo "Worker#{$workerId} is stopped\n";
        });

        $pool->start();
    }

    public function kd()
    {
        $day      = 7;
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        $exchange->setExchange('huobi');
        $mymoney = 50;
        $api     = $exchange->huobi;
        $coin    = $this->param('coin', 'str', 'btc');
        $this->info('初始化数据获取......');
        $oldData = $api->getKline($coin, '1000', '1minute');
        $this->info('初始化数据获取完毕');
        $last   = ['k' => '50', 'd' => '50', 'close' => 0];
        $prersv = 100;
        $data   = $buyResult = $sellResult = [];

        for ($i = 0; $i <= count($oldData); $i++) {
            $length = array_slice($oldData, max(0, $i - $day), min($i + 1, $day));
            {
                // 计算kd
                $item = array_shift(array_slice($length, -1));
                $low  = min(array_column($length, 'low'));
                $high = max(array_column($length, 'high'));
                if ($item['high'] == $item['low']) {
                    $rsv = $prersv;
                } else {
                    $rsv    = ($item['close'] - $low) / ($high - $low) * 100;
                    $prersv = $rsv;
                }
                $k = 2 / 3 * ($last['k'] ?? 50) + $rsv / 3;
                $d = 2 / 3 * ($last['d'] ?? 50) + $k / 3;
                $k = round($k, 4);
                $d = round($d, 4);
            }
            {
                //计算rsi
                $up = $down = $upnum = $downnum = 0;
                foreach ($length as $key => $v) {
                    if ($key > 0) {
                        $lastclose = $length[$key - 1]['close'];
                        if ($lastclose < $v['close']) {
                            $down += $v['close'] - $lastclose;
                            $downnum++;
                        } elseif ($lastclose > $v['close']) {
                            $up += $lastclose - $v['close'];
                            $upnum++;
                        }
                    }
                }
                if ($up == 0) {
                    $rsi = 0;
                } elseif ($down == 0) {
                    $rsi = 100;
                } else {
                    $rs  = $up / $down;
                    $rsi = round(100 * $rs / (1 + $rs), 4);
                }
            }
            // $this->info($item['time'] . ' | ' . $k . ' | ' . $d);
            $data[$item['time']] = ['time' => $item['time'], 'k' => $k, 'd' => $d, 'price' => $item['price'], 'rsv' => $rsv, 'rsi' => $rsi];
            if (count($data) > 10) {
                $first = array_shift(array_slice($data, 0, 1));
                unset($data[$first['time']]);
            }
            $last = ['k' => $k, 'd' => $d, 'close' => $item['close']];;
        }

        $this->info('初始值计算完毕');
        $selltime = $buy = $lirun = 0;
        foreach ($data as $k => $v) {
            $this->info(date('Y-m-d H:i:s') . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d']);
        }
        $preData = ['amount' => 0];
        do {
            $lastData = array_pop($api->getKline($coin, '1', '1minute'));
            if (empty($lastData['time']) || empty($lastData['price']) || $lastData['amount'] == 0 || ($preData['time'] == $lastData['time'] && $preData['amount'] == $lastData['amount'])) {
                $selltime = 0;
                sleep(1);
                continue;
            }
            $preData = $lastData;
            if (isset($oldData[$lastData['time']])) {
                $last = array_shift(array_slice($data, -2, 1));

                $oldData[$lastData['time']] = $lastData;
            } else {
                $last                       = array_shift(array_slice($data, -1, 1));
                $oldData[$lastData['time']] = $lastData;
                {
                    $v        = $last;
                    $k        = count($data) - 1;
                    $calcData = array_values($data);
                    if ($v['k'] > $v['d'] && $v['k'] <= 80 && $v['k'] >= 5 && ($v['price'] > $calcData[$k - 1]['price'] || $calcData[$k - 1]['k'] <= $calcData[$k - 1]['d'])) {
                        if (!$buy) {
                            $buyResult = $exchange->buyWithMoney($coin, $mymoney);
                            if ($buyResult) {
                                $buy = $buyResult['price'];
                                $this->info(date('Y-m-d H:i:s') . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 买进 ' . json_encode($buyResult), 'success');
                            } else {
                                echo('挂单失败错误终止'), PHP_EOL;
                            }

                        }
                    } elseif ($v['k'] <= 95 && $v['k'] < $v['d'] && ($v['price'] < $calcData[$k - 1]['price'] || $calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'])) {
                        if ($buy) {
                            $sellResult = $exchange->sell($coin, $buyResult['amount']);
                            $zhege      = round(($sellResult['price'] - $buy) / $buy, 8);
                            $lirun      += $zhege;
                            $this->info(date('Y-m-d H:i:s') . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $sellResult['price'] . ' | 卖出 | ' . $zhege . '|' . $lirun . ' ' . json_encode($sellResult), 'warning');
                            $buy = 0;
                        }
                    } else {
                        $selltime = 0;
                        $this->info(date('Y-m-d H:i:s') . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d']);
                    }
                }
            }
            $length = array_slice($oldData, -$day);
            {
                $low  = min(array_column($length, 'low'));
                $high = max(array_column($length, 'high'));
                if ($lastData['high'] == $lastData['low']) {
                    $rsv = $prersv;
                } else {
                    $rsv    = ($lastData['close'] - $low) / ($high - $low) * 100;
                    $prersv = $rsv;
                }
                $k = 2 / 3 * ($last['k'] ?? 50) + $rsv / 3;
                $d = 2 / 3 * ($last['d'] ?? 50) + $k / 3;
                $k = round($k, 4);
                $d = round($d, 4);
            }
            $data[$lastData['time']] = ['time' => $lastData['time'], 'k' => $k, 'd' => $d, 'price' => $lastData['price'], 'rsv' => $rsv];
            if (count($data) > 10) {
                $first = array_shift(array_slice($data, 0, 1));
                unset($data[$first['time']]);
            }
            {
                $v        = $data[$lastData['time']];
                $k        = count($data) - 1;
                $calcData = array_values($data);

                if ($v['k'] > $v['d'] && $v['k'] <= 80 && $v['k'] >= 5 && ($calcData[$k - 1]['k'] <= $calcData[$k - 1]['d'] || $v['price'] > $calcData[$k - 1]['price'])) {
                    if (!$buy) {
                        $buyResult = $exchange->buyWithMoney($coin, $mymoney);
                        if ($buyResult) {
                            $buy = $buyResult['price'];
                            $this->info(date('Y-m-d H:i:s') . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 买进 ' . json_encode($buyResult), 'success');
                        } else {
                            echo('挂单失败错误终止'), PHP_EOL;
                        }
                    }
                } elseif ($v['k'] < $v['d'] && $v['k'] <= 95 && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] || $v['price'] <= $calcData[$k - 1]['price'])) {
                    if ($buy) {
                        if ($selltime >= 2) {
                            $sellResult = $exchange->sell($coin, $buyResult['amount']);
                            $zhege      = round(($sellResult['price'] - $buy) / $buy, 8);
                            $lirun      += $zhege;
                            $this->info(date('Y-m-d H:i:s') . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 卖出 | ' . $zhege . '|' . $lirun . ' ' . json_encode($sellResult), 'warning');
                            $buy = 0;
                        } elseif ($selltime) {
                            $selltime++;
                            $this->info(date('Y-m-d H:i:s') . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | selltime ' . $selltime, 'info');
                        } else {
                            $selltime = 1;
                            $this->info(date('Y-m-d H:i:s') . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | selltime ' . $selltime, 'info');
                        }
                    }
                } else {
                    $selltime = 0;
                    // $this->info($v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . $v['rsi']);
                }
            }
            sleep(1);
        } while (true);
        $this->info($lirun, 'danger');
        // for ($i = count($data) - 100; $i < count($data); $i++) {
        //     $this->info($data[$i]['time'] . ' | ' . $data[$i]['k'] . ' | ' . $data[$i]['d'] . ' | ' . $data[$i]['rsv'] . ' | ' . $data[$i]['rsi'] . ' | ' . $data[$i]['price']);
        // }
    }


    protected function getPrice($price, $type = 'buy')
    {

    }

}