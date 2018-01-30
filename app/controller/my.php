<?php

namespace App\Controller;

use App\Component\Btc38;
use App\Model\Trade\Trade;
use App\Model\User\Api;
use Kuxin\Config;
use Kuxin\Controller;
use Kuxin\View;

class My extends Controller
{
    protected $uid;
    protected $key;
    protected $secret;
    /**
     * @var Btc38
     */
    protected $api;

    public function init()
    {
        $this->uid = 1;
        $apiData   = Api::I()->where(['user_id' => $this->uid])->find();
        if ($apiData) {

        }
        $this->api = new Btc38($apiData['uid'], $apiData['key'], $apiData['secret']);
    }

    public function balance()
    {
        echo '<pre>';
        var_dump($this->api->getTradeList('tmc'));

        exit;
        var_dump($this->api->getBalance());
    }

    public function book()
    {
        $data  = [];
        $coins = Config::get('coin');
        foreach ($coins as $coin => $coinname) {
            $result = $this->api->getOrderList($coin);
            if ($result['result'] == 'success') {
                foreach ($result['data'] as $item) {
                    $data[strtotime($item['time'])] = array_merge($item, [
                        'price_now' => Trade::I()->getPrice($coin),
                        'money'     => round($item['amount'] * $item['price'], 6),
                        'coin'      => sprintf('%s[%s]', $coinname, $coin),
                    ]);
                }
            }
        }
        krsort($data);

        return View::make('book', [
            'list' => $data,
        ]);
    }
}