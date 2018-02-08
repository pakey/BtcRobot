<?php

namespace App\Component\Exchange\Provider;

use App\Component\Exchange\Helper;
use Kuxin\Helper\Math;

class Huobi extends Helper
{

    public $name='huobi';

    protected $apikey;

    protected $secret;

    public $market = 'usdt';

    const API_ENDPOINT = 'https://api.huobi.pro';

    protected $times = [
        '1minute'  => '1min',
        '5minute'  => '5min',
        '15minute' => '15min',
        '30minute' => '30min',
        '60minute' => '60min',
        '1day'     => '1day',
        '1week'    => '1week',
        '1month'   => '1mon',
        '1yeer'    => '1year',
    ];

    public function __construct($apikey = '', $secret = '')
    {
        $this->apikey = $apikey;
        $this->secret = $secret;
    }

    public function setMarket($market)
    {
        $this->market = strtolower($market);
    }

    /**
     * 获取所有币种的基本价格
     * @param string $coin
     */
    public function getBasePrices($coin = '')
    {

    }

    /**
     * 获取所有币种交易对列表
     * @return array
     */
    public function getSymbols(): array
    {
        $records = $this->getJson(self::API_ENDPOINT, '/api/v1/exchangeInfo');
        $data    = [];
        foreach ($records['symbols'] as $item) {
            if ($item['quoteAsset'] != $this->market) {
                continue;
            }
            $data[] = ['coin' => $item['baseAsset'], 'market' => $item['quoteAsset']];
        }
        return $data;
    }

    /**
     * 币种挂单记录
     * @param string $coin
     * @return array
     */
    public function getOrderInfo(string $coin): array
    {
    }

    /**
     * 交易历史
     * @param string $coin
     * @return array
     */
    public function getTradeHistory(string $coin): array
    {
    }

    /**
     * K线记录
     * @param string $coin
     * @param string $interval
     * @param int    $limit
     * @return array
     */
    public function getKline(string $coin, int $limit = 500, string $interval = '1minute'): array
    {
        $interval = $this->times[$interval] ?? $this->times['1minute'];
        $param = [
            'symbol' => strtolower($coin) . $this->market,
            'period' => $interval,
            'size'   => min(2000, max(1, $limit)),
        ];
        $records = $this->getJson(self::API_ENDPOINT, '/market/history/kline', $param);
        $data    = [];
        foreach ($records['data'] as $record) {
            $record      = array_map(function ($v) {
                return Math::ScToNum($v, 8);
            }, $record);
            $time        = date('YmdHi', $record['id']);
            $data[$time] = [
                'time'   => $time,
                'open'   => $record['open'],
                'high'   => $record['high'],
                'low'    => $record['low'],
                'close'  => $record['close'],
                'volumn' => $record['amount'],
                'money'  => $record['vol'],
                'num'    => $record['count'],
            ];
        }
        ksort($data);
        return $data;
    }

}