<?php

namespace App\Component\Exchange\Provider;

use App\Component\Exchange\Kernel;
use Kuxin\Config;
use Kuxin\Helper\Math;

class Huobi extends Kernel
{

    public $name = 'huobi';

    protected $apikey;

    protected $secret;

    public $market = 'usdt';

    const API_ENDPOINT = 'https://api.huobipro.com';

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
        Config::set('http.user_agent','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36');
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
        $param    = [
            'symbol' => strtolower($coin) . $this->market,
            'period' => $interval,
            'size'   => min(2000, max(1, $limit)),
        ];
        $records  = $this->getJson(self::API_ENDPOINT, '/market/history/kline', $param);
        $data     = [];
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
                'amount' => $record['amount'],
                'money'  => $record['vol'],
                'num'    => $record['count'],
            ];
        }
        ksort($data);
        return $data;
    }

    public function getAccountStatus()
    {
        return $this->getJson(self::API_ENDPOINT, '/v1/account/accounts', $this->createSignParams([],'/v1/account/accounts'));
    }

    protected function createSignParams($param, $path, $method = 'GET')
    {
        $param = array_merge([
            'AccessKeyId'      => $this->apikey,
            'SignatureMethod'  => 'HmacSHA256',
            'SignatureVersion' => 2,
            'Timestamp'        => gmdate('Y-m-d\TH:i:s'),
        ], $param);

        $param['Signature'] = $this->createSign($param, $path, $method);
        return $param;
    }

    protected function createSign($param, $path, $method)
    {
        $u = [];
        foreach ($param as $k => $v) {
            $u[] = $k . "=" . urlencode($v);
        }
        asort($u);
        $sign_param_1 = $method . "\n" . parse_url(self::API_ENDPOINT, PHP_URL_HOST) . "\n" . $path . "\n" . implode('&', $u);
        $signature    = hash_hmac('sha256', $sign_param_1, $this->secret, true);
        return base64_encode($signature);
    }

}