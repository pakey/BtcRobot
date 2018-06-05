<?php

namespace App\Console;

use App\Component\Exchange\Exchange;
use App\Component\stock;
use App\Model\Price;
use Kuxin\Console;
use Kuxin\Helper\Collection;


class Robot extends Console
{
    public function coin()
    {
        $exchange   = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        $api        = $exchange->huobi;
        $coin       = 'eos';
        $priceModel = Price::I();
        $priceModel->delete();
        $last     = $priceModel->where(['exchange' => $api->name, 'coin' => $coin, 'market' => $api->market])->order('id desc')->limit(1)->find();
        $lastTime = $last['time'] ?? 0;

        do {
            $nowTimestamp = time();
            $nowTime      = date('YmdHi', $nowTimestamp);
            $this->info(date('Y-m-d H:i:s'));
            if (!$lastTime) {
                $limit   = 100;
                $records = $api->getKline($coin, $limit);
                $priceModel->addInfos($records, $api->name, $coin, $api->market);
            } elseif ($lastTime != $nowTime) {
                $limit = floor((time() - strtotime($lastTime . '00')) % 86400 / 60);
                if (date('s', $nowTimestamp) > 0) {
                    $limit++;
                }
                $records = $api->getKline($coin, $limit);
                $priceModel->updateInfos($records, $api->name, $coin, $api->market);
            } else {
                $limit   = 1;
                $records = $api->getKline($coin, $limit);
                $priceModel->updateInfos($records, $api->name, $coin, $api->market);
            }
            $lastTime = date('YmdHi');
            sleep(1);
        } while (true);
    }

    public function test()
    {
        $time    = '201802091712';
        $oldData = Price::I()->where(['exchange' => 'huobi', 'coin' => 'eos', 'market' => 'usdt', 'time' => ['<', $time]])->order('id desc')->limit(30)->getField('time,num,open,close,k,d', true);

        $up = $down = 0;
        $n  = 0;
        var_dump(Price::I()->getLastSql());
        foreach ($oldData as $item) {
            $n++;
            if ($item['open'] < $item['close']) {
                $up += $item['close'] - $item['open'];
            } else {
                $down += $item['open'] - $item['close'];
            }
            if ($up == 0) {
                $val = 0;
            } elseif ($down == 0) {
                $val = 100;
            } else {
                $rs  = $up / $down;
                $val = round(100 - 100 / (1 + $rs), 4);
            }
            $this->info($n . ' ' . $item['time'] . '  ' . $val);
        }
    }

    public function rsi()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        // $api      = $exchange->binance;
        // $coin     = 'poe';
        $api     = $exchange->huobi;
        $coin    = 'eos';
        $oldData = Price::I()->where(['exchange' => 'huobi', 'coin' => 'eos', 'market' => 'usdt', 'time' => ['<=', 201802082304]])->order('id desc')->limit(30)->getField('time,num,open,close,k,d', true);
        $start   = array_shift($oldData);
        var_dump($start['time']);
        $n    = $up = $down = 0;
        $last = $start;
        foreach ($oldData as $item) {
            $n++;
            if ($last['close'] < $item['close']) {
                $down += $item['close'] - $last['close'];
            } else {
                $up += $last['close'] - $item['close'];
            }
            if ($up == 0) {
                $val = 0;
            } elseif ($down == 0) {
                $val = 100;
            } else {
                $rs = $up / $down;
                var_dump($up);
                $val = round(100 * $rs / (1 + $rs), 4);
            }
            $this->info($n . ' | ' . $item['time'] . '  | ' . $val);
            $last = $item;
        }
        exit;
        $oldData = $api->getKline($coin, '30', '30minute');
        krsort($oldData);
        $start = array_shift($oldData);
        $start = array_shift($oldData);
        var_dump($start['time']);
        $n    = $up = $down = 0;
        $last = $start;
        foreach ($oldData as $item) {
            $n++;
            if ($last['close'] < $item['close']) {
                $down += $item['close'] - $last['close'];
            } else {
                $up += $last['close'] - $item['close'];
            }
            if ($up == 0) {
                $val = 0;
            } elseif ($down == 0) {
                $val = 100;
            } else {
                $rs  = $up / $down;
                $val = round(100 * $rs / (1 + $rs), 4);
            }
            $this->info($n . ' | ' . $item['time'] . '  | ' . $val);
            $last = $item;
        }
    }

    public function testrsi()
    {
        $data = [13.15, 13.08, 13.03, 13.16, 13.23, 13.23, 13.1, 12.18, 12.07, 12.00, 12.02, 11.96, 12.05, 11.57, 11.65, 11.12, 11.06, 10.97, 10.92, 10.71, 10.24, 10.76, 10.76];
        $data = [12.18, 12.07, 12.00, 12.02, 11.96, 12.05, 11.57, 11.65, 11.12, 11.06, 10.97, 10.92, 10.71, 10.24, 10.76, 10.76];
        rsort($data);
        $last = 10.43;
        $n    = $up = $down = $upnum = $downnum = 0;
        foreach ($data as $item) {
            $n++;
            if ($last < $item) {
                $down += $item - $last;
                $downnum++;
            } elseif ($last > $item) {
                $up += $last - $item;
                $upnum++;
            }
            if ($up == 0) {
                $val = 0;
            } elseif ($down == 0) {
                $val = 100;
            } else {
                $upnum = $upnum + $downnum;
                $rs    = ($up / $upnum) / ($down / $upnum);
                $val   = round(100 * $rs / (1 + $rs), 4);
            }
            $this->info($n . '  | ' . $val);
            $last = $item;
        }
    }

    public function rule()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        // $api      = $exchange->binance;
        // $coin     = 'poe';
        $api   = $exchange->huobi;
        $coins = $this->param('coin', 'str') ? [$this->param('coin', 'str')] : $api->getSymbols();
        for ($day = 6; $day <= 12; $day++) {
            $all = 0;
            foreach ($coins as $coin) {
                $oldData = $api->getKline($coin, '1000', '1minute');
                $last    = ['k' => '50', 'd' => '50', 'close' => 0];
                $prersv  = 100;
                $data    = [];

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
                    $last                = ['k' => $k, 'd' => $d, 'close' => $item['close']];;
                }

                $lirun = $buy = 0;
                $data  = array_values($data);
                foreach ($data as $k => $v) {
                    if ($k > 20 && $v['price']) {
                        // if ($v['k'] > $v['d'] && $v['k'] <= 60 && $v['k'] >= 10 && ($data[$k - 1]['k'] <= $data[$k - 1]['d'])) {
                        //     $this->info($v['time'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . ' | ' . $v['price'] . ' | ' . $v['rsi'] . ' | 买进', 'success');
                        // } elseif ($v['k'] < $v['d'] && $v['k'] >= 40 && $v['k'] <= 95 && ($data[$k - 1]['k'] >= $data[$k - 1]['d'])) {
                        //     $this->info($v['time'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . ' | ' . $v['price'] . ' | ' . $v['rsi'] . ' | 卖出', 'warning');
                        // }
                        if ($v['k'] > $v['d'] && $v['k'] >= 5 && ($data[$k - 1]['k'] <= $data[$k - 1]['d'] && $data[$k - 2]['k'] <= $data[$k - 2]['d'] && $data[$k - 3]['k'] <= $data[$k - 3]['d'])) {
                            // $this->info($v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . $v['rsi'] . ' | 买进', 'success');
                            if ($buy) {
                                $buy = ($v['price'] + $buy) / 2;
                            } else {
                                $buy = $v['price'];
                            }
                        } elseif ($v['k'] < $v['d'] && ($data[$k - 1]['k'] >= $data[$k - 1]['d'] && ($data[$k - 1]['k'] >= $data[$k - 1]['d'] && $data[$k - 2]['k'] >= $data[$k - 2]['d'] && $data[$k - 3]['k'] >= $data[$k - 3]['d']))) {
                            if ($buy) {
                                $zhege = round(($v['price'] - $buy) / $buy, 8) * 100;
                                $lirun += $zhege;
                                // $this->info($v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . $v['rsi'] . ' | 卖出 | '.$zhege, 'warning');
                                $buy = 0;
                            }
                        } else {
                            // $this->info($v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . $v['rsi']);
                        }
                    }
                }
                $all += $lirun;
                if ($lirun >= 0) {
                    $this->info($coin . ' | ' . $lirun, 'success');
                } else {
                    $this->info($coin . ' | ' . $lirun, 'error');
                }
            }
            $this->info('day:' . $day . ' => ' . $all);
        }

        // $oldData = array_values($oldData);
        {
            include KX_ROOT . '/func.php';
            // $kd=kd($oldData);
            // foreach($oldData as $k=>$v){
            //     $this->info( $v->time . '  | ' . $kd['lines']['0']['data'][$k] . ' | ' . $kd['lines']['1']['data'][$k] );
            // }
            //
            // $this->info('-------------');
            // $kdj = kd(json_decode(json_encode($oldData)));
            // foreach ($oldData as $k => $v) {
            //     $this->info($v['time'] . '  | ' . $kdj['lines']['0']['data'][$k] . ' | ' . $kdj['lines']['1']['data'][$k]);
            // }
            // $this->info('-------------');
        }
        // for ($i = count($data) - 100; $i < count($data); $i++) {
        //     $this->info($data[$i]['time'] . ' | ' . $data[$i]['k'] . ' | ' . $data[$i]['d'] . ' | ' . $data[$i]['rsv'] . ' | ' . $data[$i]['rsi'] . ' | ' . $data[$i]['price']);
        // }
    }

    public function huobi()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        $api      = $exchange->huobi;
        $record   = $api->getAccountStatus();
        var_dump($record);
    }

    public function kd()
    {
        $day      = 7;
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        // $api      = $exchange->binance;
        // $coin     = 'poe';
        $api  = $exchange->huobi;
        $coin = $this->param('coin', 'str', 'eos');
        $this->info('初始化数据获取......');
        $oldData = $api->getKline($coin, '1000', '1minute');
        $this->info('初始化数据获取完毕');
        // $oldData = array_values($oldData);
        {
            include KX_ROOT . '/func.php';
            // $kd=kd($oldData);
            // foreach($oldData as $k=>$v){
            //     $this->info( $v->time . '  | ' . $kd['lines']['0']['data'][$k] . ' | ' . $kd['lines']['1']['data'][$k] );
            // }
            //
            // $this->info('-------------');
            // $kdj = kd(json_decode(json_encode($oldData)));
            // foreach ($oldData as $k => $v) {
            //     $this->info($v['time'] . '  | ' . $kdj['lines']['0']['data'][$k] . ' | ' . $kdj['lines']['1']['data'][$k]);
            // }
            // $this->info('-------------');
        }
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

        $this->info('初始值计算完毕');
        $selltime = $buy = $lirun = 0;
        foreach ($data as $k => $v) {
            $this->info(date('Y-m-d H:i:s') . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d']);
        }
        do {
            $lastData = array_pop($api->getKline($coin, '1', '1minute'));
            if (empty($lastData['time']) || empty($lastData['price'])) {
                continue;
            }
            if (isset($oldData[$lastData['time']])) {
                $last = array_shift(array_slice($data, -2, 1));

                $oldData[$lastData['time']] = $lastData;
            } else {
                $last = array_shift(array_slice($data, -1, 1));

                $oldData[$lastData['time']] = $lastData;
                {
                    $v        = $last;
                    $k        = count($data) - 1;
                    $calcData = array_values($data);

                    if ($v['price'] > $calcData[$k - 1]['price'] && $v['k'] > $v['d'] && $v['k'] <= 40 && $v['k'] >= 5 && ($calcData[$k - 1]['k'] <= $calcData[$k - 1]['d'] && $calcData[$k - 2]['k'] <= $calcData[$k - 2]['d'])) {
                        if (!$buy) {
                            $this->info(date('Y-m-d H:i:s') . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 买进', 'success');
                            $buy = $v['price'];
                        }
                    } elseif ($v['k'] < $v['d'] && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] && $calcData[$k - 2]['k'] >= $calcData[$k - 2]['d']))) {
                        if ($buy) {
                            $zhege = round(($v['price'] - $buy) / $buy, 8);
                            $lirun += $zhege;
                            $this->info(date('Y-m-d H:i:s') . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 卖出 | ' . $zhege . ' | ' . $lirun, 'warning');
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

                if ($v['price'] > $calcData[$k - 1]['price'] && $v['k'] > $v['d'] && $v['k'] <= 40 && $v['k'] >= 5 && ($calcData[$k - 1]['k'] <= $calcData[$k - 1]['d'] && $calcData[$k - 2]['k'] <= $calcData[$k - 2]['d'] && $calcData[$k - 3]['k'] <= $calcData[$k - 3]['d'])) {
                    if (!$buy) {
                        $this->info(date('Y-m-d H:i:s') . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 买进', 'success');
                        $buy = $v['price'];
                    }
                } elseif ($v['k'] < $v['d'] && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] && ($calcData[$k - 1]['k'] >= $calcData[$k - 1]['d'] && $calcData[$k - 2]['k'] >= $calcData[$k - 2]['d'] && $calcData[$k - 3]['k'] >= $calcData[$k - 3]['d']))) {
                    if ($buy) {
                        if ($selltime >= 3) {
                            $zhege = round(($v['price'] - $buy) / $buy, 8);
                            $lirun += $zhege;
                            $this->info(date('Y-m-d H:i:s') . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | 卖出 | ' . $zhege . ' | ' . $lirun, 'warning');
                            $buy = 0;
                        } elseif ($selltime) {
                            $selltime++;
                            $this->info(date('Y-m-d H:i:s') . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | selltime ' . $selltime, 'info');
                        } else {
                            $selltime = 1;
                            $this->info(date('Y-m-d H:i:s') . '|' . $v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | selltime ' . $selltime, 'info');
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


    public function testkd()
    {
        $exchange = new Exchange(HUOBI_APIKEY, HUOBI_SECRET);
        // $api      = $exchange->binance;
        // $coin     = 'poe';
        $day     = 7;
        $api     = $exchange->huobi;
        $coin    = $this->param('coin', 'str', 'eos');
        $oldData = $api->getKline($coin, '1000', '1minute');
        $last    = ['k' => '50', 'd' => '50', 'close' => 0];
        $prersv  = 100;
        $data    = [];

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
            $last                = ['k' => $k, 'd' => $d, 'close' => $item['close']];;
        }

        $lirun = $buy = 0;
        $data  = array_values($data);
        foreach ($data as $k => $v) {
            if ($k > 20 && $v['price']) {
                // if ($v['k'] > $v['d'] && $v['k'] <= 60 && $v['k'] >= 10 && ($data[$k - 1]['k'] <= $data[$k - 1]['d'])) {
                //     $this->info($v['time'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . ' | ' . $v['price'] . ' | ' . $v['rsi'] . ' | 买进', 'success');
                // } elseif ($v['k'] < $v['d'] && $v['k'] >= 40 && $v['k'] <= 95 && ($data[$k - 1]['k'] >= $data[$k - 1]['d'])) {
                //     $this->info($v['time'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . ' | ' . $v['price'] . ' | ' . $v['rsi'] . ' | 卖出', 'warning');
                // }
                if ($v['price']>$data[$k - 1]['price'] && $v['k'] > $v['d'] && $v['k'] >= 5 && ($data[$k - 1]['k'] <= $data[$k - 1]['d'] && $data[$k - 2]['k'] <= $data[$k - 2]['d'] && $data[$k - 3]['k'] <= $data[$k - 3]['d'])) {
                    $this->info($v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . ' | ' . $v['rsi'] . ' | 买进', 'success');
                    if ($buy) {
                        $buy = ($v['price'] + $buy) / 2;
                    } else {
                        $buy = $v['price'];
                    }
                } elseif ($v['k'] < $v['d'] && ($data[$k - 1]['k'] >= $data[$k - 1]['d'] && ($data[$k - 1]['k'] >= $data[$k - 1]['d'] && $data[$k - 2]['k'] >= $data[$k - 2]['d'] && $data[$k - 3]['k'] >= $data[$k - 3]['d']))) {
                    if ($buy) {
                        $zhege = round(($v['price'] - $buy) / $buy, 8) * 100;
                        $lirun += $zhege;
                        $this->info($v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . ' | ' . $v['rsi'] . ' | 卖出 | ' . $zhege, 'warning');
                        $buy = 0;
                    }
                } else {
                    $this->info($v['time'] . ' | ' . $v['price'] . ' | ' . $v['k'] . ' | ' . $v['d'] . ' | ' . $v['rsv'] . ' | ' . $v['rsi']);
                }
            }
        }
        if ($lirun >= 0) {
            $this->info($coin . ' | ' . $lirun, 'success');
        } else {
            $this->info($coin . ' | ' . $lirun, 'error');
        }

    }
}
