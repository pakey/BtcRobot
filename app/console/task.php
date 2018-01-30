<?php

namespace App\Console;

use App\Component\Btc38;
use Kuxin\Console;
use Kuxin\DI;

class Task extends Console
{

    public function trade()
    {
        $coin = $this->param('coin', 'str');
        if (!$coin) {
            $this->info('请输入coin参数', 'error');
            exit;
        }
        $api = new Btc38();

        /** @var \App\Model\Price\Tmc $modelName */
        $modelName = 'App\\Model\\Trade\\' . ucfirst($coin);
        /** @var \App\Model\Trade\Tmc $trade */
        $trade = $modelName::I();
        while (true) {
            $lasttid = max(1, $trade->order('id desc')->getField('tid'));
            $records = $api->getHistoryList($coin, $lasttid);
            $data    = [];
            foreach ($records as $record) {
                $data[] = [
                    'tid'       => $record['tid'],
                    'datetime'  => date('Y-m-d H:i:s', $record['date']),
                    'minute'    => date('YmdHi', $record['date']),
                    'timestamp' => $record['date'],
                    'day'       => date('Ymd', $record['date']),
                    'price'     => $record['price'],
                    'amount'    => $record['amount'],
                    'money'     => round($record['price'] * $record['amount'], 6),
                    'type'      => $record['type'] == 'sell' ? 1 : 0,
                ];
            }
            if ($data) {
                $trade->insertAll($data);
            }
            //            echo $lasttid, '----', Math::file_size_format((memory_get_usage() - $mem)), PHP_EOL;

            DI::Storage('log')->write($coin . '_trade', date('Y-m-d H:i:s'));
            echo $lasttid, PHP_EOL;
            if (count($records) < 10) {
                sleep(20);
            } else {
                sleep(1);
            }
        }
    }

    public function price()
    {
        $coin = $this->param('coin', 'str');
        if (!$coin) {
            $this->info('请输入coin参数', 'error');
            exit;
        }


        /** @var \App\Model\Price\Tmc $modelName */
        $modelName = 'App\\Model\\Trade\\' . ucfirst($coin);
        /** @var \App\Model\Trade\Tmc $trade */
        $trade = $modelName::I();


        $modelName = 'App\\Model\\Price\\' . ucfirst($coin);
        /** @var \App\Model\Price\Tmc $model */
        $model = $modelName::I();
        list($last, $llast) = $model->order('id desc')->limit(2)->select();
        while (true) {
            if (!$last) {
                //初始化
                $first = $trade->order('id asc')->find();
                if (!$first) {
                    DI::Storage('log')->write($coin . '_price', date('Y-m-d H:i:s'));
                    echo date('Y-m-d H:i:s'), ' sleep', PHP_EOL;
                    sleep(30);
                    continue;
                }
                $timestamp = $first['timestamp'];
                $model->createRecord($timestamp);
            } else {
                $timestamp = strtotime($last['datetime']);
                if (date('Y-m-d H:i', $timestamp) == date('Y-m-d H:i')) {
                    //当前时间段
                    $timestamp = strtotime(date('Y-m-d H:i:00'));
                    sleep(10);
                } else {
                    $model->createRecord($timestamp + 60);
                }
                $records = $trade->where(['minute' => date('YmdHi', $timestamp)])->select();
                if ($llast) {
                    $open = $close = $high = $lower = $llast['close'];
                } else {
                    $open = $close = $high = $lower = 0;
                }
                $num = $sell_num = $buy_num = $money = $amount = $sell_money = $sell_amount = $buy_money = $buy_amount = 0;
                foreach ($records as $k => $v) {
                    $money  += $v['money'];
                    $amount += $v['amount'];
                    $close  = $v['price'];
                    $num++;
                    if ($v['type'] == 1) {
                        $sell_money  += $v['money'];
                        $sell_amount += $v['amount'];
                        $sell_num++;
                    } else {
                        $buy_money  += $v['money'];
                        $buy_amount += $v['amount'];
                        $buy_num++;
                    }
                    if ($v['price'] > $high) {
                        $high = $v['price'];
                    }
                    if ($v['price'] < $lower) {
                        $lower = $v['price'];
                    }
                }
                $avg  = $money ? round($money / $amount, 2) : $open;
                $data = compact('open', 'close', 'high', 'lower', 'amount', 'money', 'sell_amount', 'sell_money', 'buy_amount', 'buy_money', 'num', 'buy_num', 'sell_num', 'avg');
                $model->where(['id' => $last['id']])->update($data);
                $llast = array_merge($last, $data);

            }
            $last = $model->order('id desc')->find();

            DI::Storage('log')->write($coin . '_price', date('Y-m-d H:i:s'));
            echo $last['datetime'], PHP_EOL;
        }
    }

    public function book()
    {
        $coin = $this->param('coin', 'str');
        if (!$coin) {
            $this->info('请输入coin参数', 'error');
            exit;
        }
        $api = new Btc38();
        while (true) {
            $newBuyData = $newSellData = [];
            $buyData    = (array)DI::Cache()->get($coin . '_book_buy');
            $sellData   = (array)DI::Cache()->get($coin . '_book_sell');
            $records    = $api->getBookList($coin);
            $price      = 0;
            foreach ($records['bids'] as $item) {
                list($price, $amount) = $item;
                $key = (string)$price;
                if (isset($buyData[$key])) {
                    if ($buyData[$key]['amount'] == $amount) {
                        $newBuyData[$key] = $buyData[$key];
                    } else {
                        $newBuyData[$key] = [
                            'amount'    => $item['1'],
                            'preamount' => $buyData[$key]['amount'],
                            'money'     => round($item[0] * $item['1'], 6),
                            'time'      => date('Y-m-d H:i:s'),
                            'type'      => ($buyData[$key]['amount'] > $amount) ? '↓' : '↑',
                        ];
                    }
                } else {
                    $newBuyData[$key] = [
                        'amount'    => $item['1'],
                        'preamount' => 0,
                        'money'     => round($item[0] * $item['1'], 6),
                        'time'      => date('Y-m-d H:i:s'),
                        'type'      => '-',
                    ];
                }
            }
            foreach ($buyData as $k => $v) {
                if ($k < $price) {
                    $newBuyData[$k] = $v;
                }
            }
            DI::Cache()->set($coin . '_book_buy', $newBuyData);

            foreach ($records['asks'] as $item) {
                list($price, $amount) = $item;
                $key = (string)$price;
                if (isset($sellData[$key])) {
                    if ($sellData[$key]['amount'] == $amount) {
                        $newSellData[$key] = $sellData[$key];
                    } else {
                        $newSellData[$key] = [
                            'amount'    => $item['1'],
                            'preamount' => $sellData[$key]['amount'],
                            'money'     => round($item[0] * $item['1'], 6),
                            'time'      => date('Y-m-d H:i:s'),
                            'type'      => ($sellData[$key]['amount'] > $amount) ? '↓' : '↑',
                        ];
                    }
                } else {
                    $newSellData[$key] = [
                        'amount'    => $item['1'],
                        'preamount' => 0,
                        'money'     => round($item[0] * $item['1'], 6),
                        'time'      => date('Y-m-d H:i:s'),
                        'type'      => '-',
                    ];
                }
            }
            foreach ($sellData as $k => $v) {
                if ($k > $price) {
                    $newSellData[$k] = $v;
                }
            }

            DI::Cache()->set($coin . '_book_sell', $newSellData);
            DI::Storage('log')->write($coin . '_book', date('Y-m-d H:i:s'));
            echo count($newBuyData) . '----' . count($newSellData), PHP_EOL;
            sleep(1);
        }
    }

}