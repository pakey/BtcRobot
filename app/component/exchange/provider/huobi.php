<?php

namespace App\Component\Exchange\Provider;

use App\Component\Exchange\Helper;
use Kuxin\Helper\Math;

class Huobi extends Helper
{
    protected $apikey;

    protected $secret;

    protected $market = 'USD';

    const API_ENDPOINT = 'https://api.huobi.pro';

    public function __construct($apikey = '', $secret = '')
    {
        $this->apikey = $apikey;
        $this->secret = $secret;
    }

    public function setMarket($market)
    {
        $this->market = $market;
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
     * @param string $startTime
     * @param string $endTime
     * @return array
     */
    public function getKline(string $coin, int $limit = 500, string $interval = '1m', string $startTime = '', string $endTime = ''): array
    {
        $param = [
            'symbol'   => strtoupper($coin . $this->market),
            'interval' => $interval,
            'limit'    => $limit,
        ];
        if ($startTime) {
            $param['startTime'] .= '000';
        }
        if ($endTime) {
            $param['endTime'] .= '000';
        }

        $records = $this->getJson(self::API_ENDPOINT, '/api/v1/klines', $param);
        $data    = [];
        foreach ($records as $record) {
            $record      = array_map(function ($v) {
                return Math::ScToNum($v, 8);
            }, $record);
            $time        = date('YmdHi', substr($record[0], 0, -3));
            $data[$time] = [
                'time'        => $time,
                'open'        => $record['1'],
                'hign'        => $record['2'],
                'low'         => $record['3'],
                'close'       => $record['4'],
                'volumn'      => $record['5'],
                'money'       => $record['7'],
                'num'         => $record['8'],
                'buy_volumn'  => $record['9'],
                'buy_money'   => $record['10'],
                'sell_volumn' => $record['5'] - $record['9'],
                'sell_money'  => $record['7'] - $record['10'],
            ];
        }
        return $data;
    }

}