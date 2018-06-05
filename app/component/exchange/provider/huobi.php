<?php

namespace App\Component\Exchange\Provider;

use App\Component\Exchange\Kernel;
use Kuxin\Config;
use Kuxin\Helper\Http;
use Kuxin\Helper\Json;
use Kuxin\Helper\Math;

class Huobi extends Kernel
{

    const BUY = 'buy-limit';
    const BUY_LIMIT = 'buy-limit';
    const BUY_MARKET = 'buy-market';
    const BUY_IOC = 'buy-ioc';
    const SELL_MARKET = 'sell-market';
    const SELL_LIMIT = 'sell-limit';
    const SELL = 'sell-limit';
    const SELL_IOC = 'sell-ioc';

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
        Config::set('http.user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36');

    }


    protected function postJson(string $endpoint, string $path, array $params = []): array
    {
        $httpResult = Http::post($endpoint . $path . '?' . http_build_query($this->createSignParams([], $path, 'POST')), Json::encode($params), ['Content-Type' => 'application/json']);
        $jsonResult = Json::decode($httpResult);
        return $jsonResult ?: [];
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
        $records = $this->getJson(self::API_ENDPOINT, '/v1/common/symbols');
        $data    = [];
        foreach ($records['data'] as $item) {
            if ($item['quote-currency'] != $this->market) {
                continue;
            }
            $data[] = $item['base-currency'];
        }
        return $data;
    }

    /**
     * 获取单个币种交易信息
     * @return array
     */
    public function getSymbolInfo(string $coin): array
    {
        static $records = [];
        if (!$records) {
            $records = $this->getJson(self::API_ENDPOINT, '/v1/common/symbols');
        }
        foreach ($records['data'] as $item) {
            if ($item['quote-currency'] == $this->market && $item['base-currency'] == $coin) {
                return [
                    'name'   => $coin,
                    'market' => $this->market,
                    'price'  => $item['price-precision'],
                    'amount' => $item['amount-precision'],
                ];
            }
        }
        trigger_error('找不到的货币信息', E_ERROR);
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
     * 交易历史
     * @param string $coin
     * @return array
     */
    public function getTradeDepth(string $coin): array
    {
        $param = [
            'type'   => 'step0',
            'symbol' => $coin . $this->market,
        ];
        do {
            $records = $this->getJson(self::API_ENDPOINT, '/market/depth', $param);
            if ($records['status'] == 'ok') {
                break;
            }
            sleep(0.1);
        } while (true);

        return $records['tick'];
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
            'size'   => min(1000, max(1, $limit)),
        ];
        $records  = $this->getJson(self::API_ENDPOINT, '/market/history/kline', $param);
        $data     = [];
        $price    = 0;
        foreach ($records['data'] as $record) {
            $record      = array_map(function ($v) {
                return Math::ScToNum($v, 8);
            }, $record);
            $time        = date('YmdHi', $record['id']);
            $price       = $record['amount'] ? Math::ScToNum($record['vol'] / $record['amount'], 8) : $price;
            $data[$time] = [
                'time'   => $time,
                'open'   => $record['open'],
                'high'   => $record['high'],
                'low'    => $record['low'],
                'close'  => $record['close'],
                'amount' => $record['amount'],
                'money'  => $record['vol'],
                'num'    => $record['count'],
                'price'  => $price,
            ];
        }
        ksort($data);
        return $data;
    }

    public function getAccountStatus()
    {
        return $this->getJson(self::API_ENDPOINT, '/v1/account/accounts', $this->createSignParams([], '/v1/account/accounts'));
    }

    public function getAccountIds($coin = null, $market = null)
    {
        $path    = '/v1/account/accounts';
        $records = $this->getJson(self::API_ENDPOINT, $path, $this->createSignParams([], $path));
        $data    = [];
        foreach ($records['data'] as $record) {
            if ($record['type'] == 'spot') {
                $data[$record['subtype']] = $record['id'];
            }
        }
        if ($coin) {
            $market = $market ?? $this->market;
            return $data[$coin . $market] ?? "";
        }
        return $data;
    }

    public function getBalance($coin, $market = null)
    {
        $accountId = HUOBI_SPOT_ID;
        $path      = "/v1/account/accounts/{$accountId}/balance";
        $records   = $this->getJson(self::API_ENDPOINT, $path, $this->createSignParams([], $path));
        $result    = [];
        foreach ($records['data']['list'] as $k => $v) {
            if ($v['currency'] == $coin) {
                $result[$v['type']] = $v['balance'];
            }
        }
        return $result;
    }

    public function orderSubmit($coin, $amount = 0, $price = 0, $type = self::BUY_LIMIT)
    {
        $path  = '/v1/order/orders/place';
        $param = [
            'account-id' => HUOBI_SPOT_ID,
            'amount'     => (string)$amount,
            'symbol'     => $coin . $this->market,
            'type'       => $type,
            'source'     => 'api',
        ];
        switch ($type) {
            case self::BUY_LIMIT:
            case self::SELL_LIMIT:
                $param['price'] = (string)$price;
                break;
        }
        $records = $this->postJson(self::API_ENDPOINT, $path, $param);
        if ($records['status'] != 'ok') {
            var_dump($this->getBalance($coin));
            var_dump($records, $param);
            debug_print_backtrace(2);
            return false;
        }
        return $records['data'];
    }

    public function orderInfo($orderId)
    {
        $path    = '/v1/order/orders/' . $orderId;
        $records = $this->getJson(self::API_ENDPOINT, $path, $this->createSignParams([], $path));
        return $records;
    }

    public function orderCancel($orderId)
    {
        $path    = '/v1/order/orders/' . $orderId . '/submitcancel';
        $records = $this->postJson(self::API_ENDPOINT, $path, $this->createSignParams([], $path));
        return $records;
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