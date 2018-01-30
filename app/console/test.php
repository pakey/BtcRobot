<?php

namespace App\Console;

use App\Component\Btc38;
use App\Model\Price\Price;
use App\Model\Price\Tmc;
use App\Model\Tool\Auto;
use App\Model\Trade\Trade;
use Kuxin\Config;
use Kuxin\Console;
use Kuxin\DI;

class Test extends Console
{

    public function index()
    {
        $api = new Btc38(Config::get('btc.uid'), Config::get('btc.key'), Config::get('btc.secret'));
        var_dump($api->getInfoCount('tmc'));
        var_dump($api->getInfoCount('etc'));
        var_dump($api->getInfoCount('eth'));
        //        var_dump($api->cancelOrder('eac',368094373));
        //        var_dump($api->getTradeList('tmc', 1));
        //        var_dump($api->addOrder('eac','sell','0.01292','476576.726500'));
        //        var_dump($api->addOrder('eac','buy','0.01292','476576.726500'));
    }


    public function auto()
    {
        $coindata = [
            'tmc'  => [
                'fee'         => 0.998,
                'minprice'    => 0.001,
                'minbuyprice' => 8.5,
                'maxbuyprice' => 10,
                'maxbook'     => 1,
            ],
            'tag'  => [
                'fee'         => 0.999,
                'minprice'    => 0.01,
                'minbuyprice' => 2.2,
                'maxbuyprice' => 2.7,
                'maxbook'     => 1,
            ],
            'anc'  => [
                'fee'         => 0.999,
                'minprice'    => 0.001,
                'minbuyprice' => 8,
                'maxbuyprice' => 8.8,
                'maxbook'     => 1,
            ],
            'mec'  => [
                'fee'         => 0.999,
                'minprice'    => 0.001,
                'minbuyprice' => 1.2,
                'maxbuyprice' => 1.5,
                'maxbook'     => 1,
            ],
            'xcn'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.085,
                'maxbuyprice' => 0.1,
                'maxbook'     => 1,
            ],
            'nxt'  => [
                'fee'         => 0.999,
                'minprice'    => 0.001,
                'minbuyprice' => 0.5,
                'maxbuyprice' => 0.6,
                'maxbook'     => 1,
            ],
            'wdc'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.4,
                'maxbuyprice' => 0.6,
                'maxbook'     => 1,
            ],
            'hlb'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.12,
                'maxbuyprice' => 0.16,
                'maxbook'     => 1,
            ],
            'doge' => [
                'fee'         => 0.999,
                'minprice'    => 0.00001,
                'minbuyprice' => 0.011,
                'maxbuyprice' => 0.013,
                'maxbook'     => 1,
            ],
            'eac'  => [
                'fee'         => 0.999,
                'minprice'    => 0.00001,
                'minbuyprice' => 0.0075,
                'maxbuyprice' => 0.0095,
                'maxbook'     => 1,
            ],
            'xpm'  => [
                'fee'         => 0.999,
                'minprice'    => 0.01,
                'minbuyprice' => 2,
                'maxbuyprice' => 2.8,
                'maxbook'     => 1,
            ],
            'blk'  => [
                'fee'         => 0.999,
                'minprice'    => 0.001,
                'minbuyprice' => 1.3,
                'maxbuyprice' => 1.8,
                'maxbook'     => 1,
            ],
            'zcc'  => [
                'fee'         => 0.999,
                'minprice'    => 0.001,
                'minbuyprice' => 0.25,
                'maxbuyprice' => 0.35,
                'maxbook'     => 1,
            ],
            'bts'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.75,
                'maxbuyprice' => 1,
                'maxbook'     => 1,
            ],
            'sys'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.48,
                'maxbuyprice' => 0.6,
                'maxbook'     => 1,
            ],
            'qrk'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.16,
                'maxbuyprice' => 0.22,
                'maxbook'     => 1,
            ],
            'xem'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.8,
                'maxbuyprice' => 1,
                'maxbook'     => 1,
            ],
            'xrp'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 1,
                'maxbuyprice' => 1.5,
                'maxbook'     => 1,
            ],
            'ardr' => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.5,
                'maxbuyprice' => 0.8,
                'maxbook'     => 1,
            ],
            'ppc'  => [
                'fee'         => 0.999,
                'minprice'    => 0.01,
                'minbuyprice' => 11,
                'maxbuyprice' => 13.5,
                'maxbook'     => 1,
            ],
            'vash' => [
                'fee'         => 0.999,
                'minprice'    => 0.001,
                'minbuyprice' => 0.085,
                'maxbuyprice' => 1,
                'maxbook'     => 1,
            ],
            'med'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.4,
                'maxbuyprice' => 0.6,
                'maxbook'     => 1,
            ],
            'xlm'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.11,
                'maxbuyprice' => 0.15,
                'maxbook'     => 1,
            ],
            'xzc'  => [
                'fee'         => 0.999,
                'minprice'    => 0.01,
                'minbuyprice' => 44,
                'maxbuyprice' => 50,
                'maxbook'     => 1,
            ],
            'ncs'  => [
                'fee'         => 0.999,
                'minprice'    => 0.0001,
                'minbuyprice' => 0.18,
                'maxbuyprice' => 0.26,
                'maxbook'     => 1,
            ],
            'mgc'  => [
                'fee'         => 0.999,
                'minprice'    => 0.001,
                'minbuyprice' => 0.4,
                'maxbuyprice' => 0.6,
                'maxbook'     => 1,
            ],

        ];
        //        $coindata = [
        //            'xpm' => [
        //                'fee'         => 0.999,
        //                'minprice'    => 0.01,
        //                'minbuyprice' => 2,
        //                'maxbuyprice' => 2.8,
        //                'maxbook'     => 1,
        //            ],
        //        ];
        $money = 120;
        $model = Auto::I();
        $api   = new Btc38(Config::get('btc.uid'), Config::get('btc.key'), Config::get('btc.secret'));
        $this->info('启动中......');
        while (true) {
            $this->info(PHP_EOL . date('Y-m-d H:i:s'));
            foreach ($coindata as $coin => $d) {
                $fee         = $d['fee'];
                $minprice    = $d['minprice'];
                $minbuyprice = $d['minbuyprice'];
                $maxbuyprice = $d['maxbuyprice'];
                $maxbook     = $d['maxbook'];
                //获取价格
                $records = $api->getBookList($coin);
                $buyData = $sellData = [];
                foreach ($records['bids'] as $item) {
                    list($price, $amount) = $item;
                    $key           = (string)$price;
                    $buyData[$key] = ['amount' => $amount];
                }
                foreach ($records['asks'] as $item) {
                    list($price, $amount) = $item;
                    $key            = (string)$price;
                    $sellData[$key] = ['amount' => $amount];
                }
                //计算买入价和卖出价
                $buyPrice = $sellPrice = 0;
                foreach ($buyData as $k => $v) {
                    if ($v['amount'] * $k > $money) {
                        $buyPrice = $k;
                        break;
                    }
                }
                foreach ($sellData as $k => $v) {
                    if ($v['amount'] * $k > $money) {
                        $sellPrice = $k;
                        break;
                    }
                }
                if (!$buyPrice || !$sellPrice) {
                    $this->info($coin . ' 获取价格行情失败', 'error');
                    continue;
                }
                $buyPrice  = (float)$buyPrice;
                $sellPrice = (float)$sellPrice;
                $this->info($coin . ' 获取买入价卖出价 buy：' . $buyPrice . ' sell：' . $sellPrice . ' 价差:' . round(($sellPrice - $minprice) / ($buyPrice + $minprice) - 1, 4) * 100);
                $sellRecords = $model->where(['coin' => $coin, 'sell_status' => 0, 'buy_status' => 1])->select();
                $buyRecords  = $model->where(['coin' => $coin, 'buy_status' => 0])->select();
                $this->info($coin . ' 当前买入队列：' . count($buyRecords) . ' 卖出队列：' . count($sellRecords));
                if ($buyPrice && $sellPrice) {
                    //获取挂单列表
                    $return = $api->getOrderList($coin);
                    if ($return['result'] == 'success') {
                        $booklist = array_column($return['data'], null, 'id');
                    } else if ($return['info'] == 'no_order') {
                        $booklist = [];
                    } else {
                        $this->info($return['info'], 'error');
                        continue;
                    }
                    //检查卖单是否有成交的
                    //                    $this->info($coin . ' 检测卖单成交......');
                    foreach ($sellRecords as $k => $v) {
                        if (!isset($booklist[$v['sell_order_id']])) {
                            $this->info($coin . ' 卖单成交 价格：' . $v['sell_price'] . ' order_id:' . $v['sell_order_id'], 'success');
                            //已成交
                            $income = round($v['sell_price'] * $v['sell_amount'] * $fee - $v['buy_price'] * $v['buy_amount'], 6);
                            $model->where(['id' => $v['id']])->update(['sell_status' => 1, 'income' => $income]);
                        }
                    }
                    //检查买单是否有成交的 成交则挂卖单
                    // $this->info($coin . ' 检测买单成交......');
                    foreach ($buyRecords as $k => $v) {
                        if (!isset($booklist[$v['buy_order_id']])) {
                            $this->info($coin . ' 买单成交 价格：' . $v['buy_price'] . ' order_id:' . $v['buy_order_id'], 'success');
                            $data = [
                                'buy_status'  => 1,
                                'sell_amount' => round($v['buy_amount'] * $fee, 6),
                                'sell_price'  => $sellPrice - $minprice,
                                'sell_status' => 0,
                                'sell_date'   => date('Y-m-d H:i:s'),
                                'sell_clear'  => round($v['buy_price'] * 0.98, 6),
                            ];

                            //已成交挂卖单
                            $res = $api->addOrder($coin, 'sell', $data['sell_price'], $data['sell_amount']);
                            if ($res['result'] == 'success') {
                                $data['sell_order_id'] = (int)$res['data'];
                            } else {
                                $this->info($coin . ' 挂卖单失败 ' . $res['info'], 'error');
                                DI::Storage('log')->write('cron', date('Y-m-d H:i:s') . ' ' . json_encode($res) . PHP_EOL);
                                $model->where(['id' => $v['id']])->update(['buy_status' => -2]);
                                continue;
                            }
                            $this->info($coin . ' 挂卖单 价格：' . $data['sell_price'] . ' order_id:' . $data['sell_order_id'], 'info');
                            $model->where(['id' => $v['id']])->update($data);
                        }
                    }
                    //判断是否需要取消买单
                    //                    $this->info($coin . ' 检测需要取消买单......');
                    $buyRecords = $model->where(['coin' => $coin, 'buy_status' => 0])->select();
                    foreach ($buyRecords as $v) {
                        $v['buy_price'] = floatval($v['buy_price']);
                        if (
                            $buyPrice - $minprice > $v['buy_price']
                            || $v['buy_price'] - $minprice > $buyPrice
                            || ($v['buy_price'] * 1.006 > $sellPrice)
                        ) {
                            $api->cancelOrder($coin, $v['buy_order_id']);
                            $model->where(['id' => $v['id']])->update(['buy_status' => -1, 'sell_status' => -1]);
                            $this->info($coin . ' 取消买单 买单价格：' . $v['buy_price'] . ' 当前价格： ' . $buyPrice . ' order_id:' . $v['buy_order_id'], 'warning');
                        }
                    }

                    //判断是否需要取消卖单
                    $sellRecords = $model->where(['coin' => $coin, 'buy_status' => 1, 'sell_status' => 0])->select();
                    foreach ($sellRecords as $v) {
                        if ($sellPrice < $v['sell_price'] || $v['sell_price'] + $minprice < $sellPrice) {
                            $api->cancelOrder($coin, $v['sell_order_id']);
                            $this->info($coin . ' 取消卖单 卖单价格：' . $v['sell_price'] . ' 当前价格： ' . $sellPrice . ' order_id:' . $v['sell_order_id'], 'warning');
                            $data = [
                                'sell_price'  => $sellPrice - $minprice,
                                'sell_status' => 0,
                                'sell_date'   => date('Y-m-d H:i:s'),
                            ];
                            $res  = $api->addOrder($coin, 'sell', $data['sell_price'], $v['sell_amount']);
                            if ($res['result'] == 'success' && $res['data']) {
                                $data['sell_order_id'] = $res['data'];
                            } else {
                                $this->info($coin . ' 挂卖单失败 ' . $res['info'], 'error');
                                DI::Storage('log')->write('cron', date('Y-m-d H:i:s') . ' ' . json_encode($res) . PHP_EOL);
                                $model->where(['id' => $v['id']])->update(['buy_status' => -2]);
                                continue;
                            }
                            $this->info($coin . ' 挂卖单 价格：' . $data['sell_price'] . ' order_id:' . $data['sell_order_id'], 'info');
                            $model->where(['id' => $v['id']])->update($data);
                        }
                    }
                    //判断是否需要止损 ( todo 考虑成交价 )
                    //                    $this->info($coin . ' 检测需要止损......');
                    foreach ($sellRecords as $record) {
                        if ($sellPrice <= $record['buy_price']) {
                            //达到止损条件
                            $res = $api->cancelOrder($coin, $record['sell_order_id']);
                            if ($res['result'] == 'success') {
                                $res = $api->addOrder($coin, 'sell', $buyPrice, $record['sell_amount']);
                                if ($res['result'] == 'success') {
                                    $model->where(['id' => $record['id']])->update(['sell_price' => $buyPrice]);
                                    $this->info($coin . ' 到达强制止损警戒线 止损价格：' . $buyPrice . ' 当前价格： ' . $sellPrice, 'error');
                                    continue;
                                }
                            }
                            $this->info($coin . ' 止损 止损失败：' . $record['id'] . ' 当前价格： ' . $sellPrice, 'error');
                        }
                    }

                    //$this->info($coin . ' 检测是否可以新挂买单......');
                    if (1 || ($buyPrice < $maxbuyprice && $buyPrice > $minbuyprice)) {
                        //判断是否需要重新挂买单
                        if ($model->where("coin='{$coin}' and (buy_status=0)")->count() < 1 && $model->where("coin='{$coin}' and (buy_status=0 or (sell_status=0 and buy_status=1))")->count() < $maxbook) {
                            //判断是否价格是否符合买单条件
                            $this->info($coin . ' 当前挂单队列有空余，可以挂买单');
                            if (($buyPrice + $minprice) * 1.01 < ($sellPrice - $minprice) && ($sellPrice - $buyPrice) / $minprice > 5) {
                                //挂买单
                                $data = [
                                    'coin'       => $coin,
                                    'buy_date'   => date('Y-m-d H:i:s'),
                                    'buy_price'  => $buyPrice + $minprice,
                                    'buy_amount' => floor($money / ($buyPrice + $minprice)),
                                    'buy_status' => 0,
                                ];
                                $res  = $api->addOrder($coin, 'buy', $data['buy_price'], $data['buy_amount']);
                                if ($res['result'] == 'success') {
                                    $data['buy_order_id'] = (int)$res['data'];
                                } else {
                                    $this->info($coin . ' 挂买单失败 ' . $res['info'], 'error');
                                }
                                $model->insert($data);
                                $this->info($coin . ' 挂买单成功，价格：' . $data['buy_price'] . ' order_id：' . $data['buy_order_id'], 'info');
                            } else {
                                $this->info($coin . ' 价差不符合，放弃挂买单');
                            }
                        } else {
                            $this->info($coin . ' 当前买入挂单队列已满');
                        }
                    } else {
                        $this->info($coin . ' 未在安全价格范围内 min:' . $minbuyprice . ' max:' . $maxbuyprice);
                    }

                }
            }
            //            if ($model->where('sell_status=1 and income<0')->count() > 15) {
            //                $this->info('止损到达指定次数，结束程序！');
            //                exit;
            //            }

            sleep(1);
        }
    }

    public function stat()
    {
        DI::Storage('log')->write('tmc_trade', date('Y-m-d H:i:s'));
        var_dump(DI::Storage('log')->read('tmc_trade'));
    }


    public function db()
    {
        $items = [
            "inf" => "讯链",
        ];
        foreach ($items as $coin => $name) {
            $str  = <<<SQL
CREATE TABLE `trade_{$coin}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` bigint(11) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `minute` bigint(20) NOT NULL DEFAULT '0',
  `day` int(11) NOT NULL DEFAULT '0',
  `price` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `money` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 buy 1sell',
  PRIMARY KEY (`id`),
  KEY `minute` (`minute`),
  KEY `day` (`day`)
) ENGINE=Innodb  DEFAULT CHARSET=utf8;

CREATE TABLE `price_{$coin}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `month` int(11) NOT NULL DEFAULT '0',
  `week` int(11) NOT NULL DEFAULT '0',
  `day` int(11) NOT NULL DEFAULT '0',
  `hour` int(11) NOT NULL DEFAULT '0',
  `minute30` int(11) NOT NULL DEFAULT '0',
  `minute15` int(11) NOT NULL DEFAULT '0',
  `minute10` int(11) NOT NULL DEFAULT '0',
  `minute5` int(11) NOT NULL DEFAULT '0',
  `minute` int(11) NOT NULL DEFAULT '0',
  `open` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `close` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `high` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `lower` decimal(12,6) NOT NULL DEFAULT '0.000000',
  `avg` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `money` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `num` int(11) NOT NULL DEFAULT '0',
  `sell_money` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `sell_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `sell_num` int(11) NOT NULL DEFAULT '0',
  `buy_money` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `buy_amount` decimal(20,6) NOT NULL DEFAULT '0.000000',
  `buy_num` int(11) NOT NULL DEFAULT '0',
  `usernum` int(11) NOT NULL DEFAULT '0',
  `totalcoin` decimal(20,6) NOT NULL DEFAULT '0.000000',
  PRIMARY KEY (`id`),
  KEY `month` (`month`),
  KEY `week` (`week`),
  KEY `day` (`day`),
  KEY `hour` (`hour`),
  KEY `minute30` (`minute30`),
  KEY `minute15` (`minute15`),
  KEY `minute10` (`minute10`),
  KEY `minute5` (`minute5`),
  KEY `minute` (`minute`),
  KEY `month_2` (`month`),
  KEY `amount` (`amount`)
) ENGINE=Innodb  DEFAULT CHARSET=utf8;
SQL;
            $name = ucfirst($coin);
            $file = <<<PHP
<?php

namespace App\Model\Price;

class {$name} extends Price
{
    protected \$table = 'price_{$coin}';
}
PHP;
            file_put_contents(KX_ROOT . '/app/model/price/' . $coin . '.php', $file);
            $file = <<<PHP
<?php

namespace App\Model\Trade;

class {$name} extends Trade
{
    protected \$table = 'trade_{$coin}';
}
PHP;
            file_put_contents(KX_ROOT . '/app/model/trade/' . $coin . '.php', $file);

            $res = Tmc::I()->execute($str);
            echo $name, '----', (int)$res, PHP_EOL;
        }
    }

    public function fenhong()
    {
        $start = date('Ymd', time() - 7 * 86400);
        $end   = date('Ymd', time() - 86400);
        $items = Config::get('coin');
        $trade = Trade::I();
        foreach ($items as $coin => $coinname) {
            $records = $trade->table('trade_' . $coin)
                ->field('day,sum(money) money')
                ->group('day')
                ->where(['day' => ['between', [$start, $end]]])
                ->select();
            echo $coinname, '|', $coin, '|';
            foreach ($records as $record) {
                echo $record['money'] . '|';
            }
            echo PHP_EOL;
        }
    }

    public function daytotal()
    {
        $start = $this->param('day', 'int', date('Ymd'));
        $items = Config::get('coin');
        $trade = Trade::I();
        $total = 0;
        foreach ($items as $coin => $coinname) {
            $records = $trade->table('trade_' . $coin)
                ->field('day,sum(money) money')
                ->where(['day' => $start])
                ->find();
            $total   += $records['money'];
        }
        $this->info($total);
    }

    public function clear()
    {
        $items = Config::get('coin');
        $price = Price::I();
        foreach ($items as $coin => $coinname) {
            $price->table('price_' . $coin)->where(['day' => ['>=', '20170701']])->delete();
            echo $coinname, ' clear', PHP_EOL;
        }
    }

    public function cache()
    {
        DI::Cache()->set('test', 1);
        var_dump(DI::Cache()->get('test'));
    }

    public function price()
    {
        ini_set('memory_limit', '1024M');
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
        $lastclose = $llast['close'];
        while (true) {
            $timestamp = strtotime($last['datetime']);
            if (date('Y-m-d H:i', $timestamp) == date('Y-m-d H:i')) {
                //当前时间段
                echo date('Y-m-d H:i');
                exit;
            } else {
                $minute  = date('YmdHi', $timestamp);
                $records = $trade->where(['minute' => $minute])->select();
                $open    = $close = $high = $lower = $avg = $lastclose;
                $num     = $sell_num = $buy_num = $money = $amount = $sell_money = $sell_amount = $buy_money = $buy_amount = 0;
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
                if ($amount) {
                    $avg = round($money / $amount, 2);
                }
                $data      = compact('open', 'close', 'high', 'lower', 'amount', 'money', 'sell_amount', 'sell_money', 'buy_amount', 'buy_money', 'num', 'buy_num', 'sell_num', 'avg');
                $lastclose = $close;
                $model->where(['id' => $last['id']])->update($data);
                //判断下一次
                $nextTrade = $trade->where(['minute' => ['>', $minute]])->find();
                if ($timestamp + 60 >= $nextTrade['timestamp']) {
                    $timestamp += 60;
                    $model->createRecord($timestamp);
                } else {
                    $insertdata = [];
                    while ($timestamp + 60 < $nextTrade['timestamp']) {
                        $timestamp    += 60;
                        $insertdata[] = $model->getInsertData($timestamp, $lastclose);
                    }
                    $model->insertAll($insertdata);
                }
                $last = $model->order('id desc')->find();
            }
            DI::Storage('log')->write($coin . '_price', date('Y-m-d H:i:s'));
            echo $last['datetime'], PHP_EOL;
        }
    }
}