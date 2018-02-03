<?php

namespace App\Component;

use Kuxin\Helper\Http;
use Kuxin\Helper\Json;

class Btc38
{
    //某个信息
    const API_INFO = 'http://api.btc38.com/v1/ticker.php';
    const API_INFO_COUNT = 'http://www.btc38.com/trade/getCoinHold.php';
    //挂单查询
    const API_ORDER_BOOK = 'http://api.btc38.com/v1/depth.php';
    //历史成交
    const API_HISTORY = 'http://api.btc38.com/v1/trades.php';

    //我的余额
    const API_MY_BALANCE = 'http://api.btc38.com/v1/getMyBalance.php';
    //挂单列表
    const API_MY_ORDER_LIST = 'http://api.btc38.com/v1/getOrderList.php';
    //添加挂单
    const API_MY_ORDER_ADD = 'http://api.btc38.com/v1/submitOrder.php';
    //取消挂单
    const API_MY_ORDER_CANCEL = 'http://api.btc38.com/v1/cancelOrder.php';
    //成交列表
    const API_MY_TRADE_LIST = 'http://api.btc38.com/v1/getMyTradeList.php';

    //用户uid
    protected $uid;
    //交易api key
    protected $key;
    //交易api secret
    protected $secret;

    public function __construct($uid = null, $key = null, $secret = null)
    {
        $this->uid    = $uid;
        $this->key    = $key;
        $this->secret = $secret;
    }

    /**
     * 获取所有币种的信息
     * @return mixed
     */
    public function getAll()
    {
        return $this->getInfo('all');
    }

    /**
     * 获取单个币种的信息
     * @param $coin
     * @return mixed
     */
    public function getInfo($coin)
    {
        $param = [
            'c'       => $coin,
            'mk_type' => 'cny',
        ];

        return Http::jsonGet(self::API_INFO, $param);
    }

    /**
     * 获取币种的统计信息
     * @param $coin
     * @return array
     */
    public function getInfoCount($coin)
    {
        $param  = [
            'coinname' => $coin,
            't'        => microtime(true),
        ];
        $return = Http::jsonGet(self::API_INFO_COUNT, $param);

        return [
            'users'  => $return['holders'],
            'total'  => $return['totalCoins'],
            //            'avg'    => $return['coinsPerHolders'],
            //            'top10'  => explode('-', $return['top10']),
            'update' => $return['updateTime'],
        ];
    }

    /**
     * 获取挂单深度
     * @param $coin
     * @return mixed
     */
    public function getBookList($coin)
    {
        $param = [
            'c'       => $coin,
            'mk_type' => 'cny',
        ];

        return Http::jsonGet(self::API_ORDER_BOOK, $param);
    }

    /**
     * 获取成交历史
     * @param $coin
     * @param int $tid
     * @return mixed
     */
    public function getHistoryList($coin, $tid = 0)
    {
        $param = [
            'c'       => $coin,
            'mk_type' => 'cny',
        ];

        if ($tid) {
            $param['tid'] = $tid;
        }

        return Http::jsonGet(self::API_HISTORY, $param);
    }

    /**
     * 查询我的资金情况
     * @return array
     */
    public function getBalance()
    {
        $param = [
            'key'  => $this->key,
            'time' => $_SERVER['REQUEST_TIME'],
            'md5'  => $this->_genmd5(),
        ];
        $res   = Http::Post(self::API_MY_BALANCE, $param);
        if ($res{0} == '{') {
            $data   = Json::decode($res);
            $result = [];
            foreach ($data as $key => $value) {
                if (substr($key, -8) == '_balance') {
                    $result[substr($key, 0, -8)]['free'] = $value;
                } else {
                    $result[substr($key, 0, -13)]['lock'] = $value;
                }
            }
            foreach ($result as $coin => $item) {
                if ($item['free'] + $item['lock'] == 0) {
                    unset($result[$coin]);
                } else {
                    $result[$coin]['total'] = $item['free'] + $item['lock'];
                }
            }

            return [
                'result' => 'success',
                'data'   => $result,
            ];
        } else {
            return [
                'result' => 'fail',
                'info'   => $res,
            ];
        }
    }

    /**
     * 获取挂单列表
     * @param $coin
     * @return array
     */
    public function getOrderList($coin)
    {
        $param = [
            'key'      => $this->key,
            'time'     => $_SERVER['REQUEST_TIME'],
            'skey'     => $this->secret,
            'md5'      => $this->_genmd5(),
            'mk_type'  => 'cny',
            'coinname' => $coin,
        ];
        $res   = Http::Post(self::API_MY_ORDER_LIST, $param);
        if ($res{0} == '{' || $res{0} == '[') {
            $data = Json::decode($res);

            return [
                'result' => 'success',
                'data'   => $data,
                'source' => $res,
            ];
        } else {
            return [
                'result' => 'fail',
                'info'   => $res,
            ];
        }
    }

    /**
     * 挂单
     *
     * @param $coin
     * @param $type
     * @param $price
     * @param $amount
     * @return array
     */
    public function addOrder($coin, $type, $price, $amount)
    {
        $param = [
            'key'      => $this->key,
            'time'     => $_SERVER['REQUEST_TIME'],
            'md5'      => $this->_genmd5(),
            'mk_type'  => 'cny',
            'coinname' => $coin,
            'type'     => ($type == 'sell') ? 2 : 1,
            'price'    => $price,
            'amount'   => $amount,
        ];
        $res   = Http::Post(self::API_MY_ORDER_ADD, $param);
        if (substr($res, 0, 4) == 'succ') {
            return [
                'result' => 'success',
                'data'   => substr($res, 5),
                'source' => $res,
            ];
        } else {
            return [
                'result' => 'fail',
                'info'   => $res,
            ];
        }
    }

    /**
     * 取消挂单
     * @param $coin
     * @param $order_id
     * @return array
     */
    public function cancelOrder($coin, $order_id)
    {
        $param = [
            'key'      => $this->key,
            'time'     => $_SERVER['REQUEST_TIME'],
            'md5'      => $this->_genmd5(),
            'mk_type'  => 'cny',
            'coinname' => $coin,
            'order_id' => $order_id,
        ];
        $res   = Http::Post(self::API_MY_ORDER_CANCEL, $param);
        if ($res == 'succ') {
            return [
                'result' => 'success',
                'source' => $res,
            ];
        } else {
            return [
                'result' => 'fail',
                'info'   => $res,
            ];
        }
    }

    /**
     * 获取交易历史
     * @param $coin
     * @param int $page
     * @return array
     */
    public function getTradeList($coin, $page = 1)
    {
        $param = [
            'key'      => $this->key,
            'time'     => $_SERVER['REQUEST_TIME'],
            'md5'      => $this->_genmd5(),
            'mk_type'  => 'cny',
            'coinname' => $coin,
            'page'     => $page - 1,
        ];
        $res   = Http::Post(self::API_MY_TRADE_LIST, $param);
        if ($res{0} == '{' || $res{0} == '[') {
            return [
                'result' => 'success',
                'data'   => Json::decode($res),
            ];
        } else {
            return [
                'result' => 'fail',
                'info'   => $res,
            ];
        }
    }

    /**
     * 生成签名
     * @return string
     */
    private function _genmd5()
    {
        return md5($this->key . '_' . $this->uid . '_' . $this->secret . '_' . $_SERVER['REQUEST_TIME']);
    }
}