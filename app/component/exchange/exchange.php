<?php

namespace App\Component\Exchange;

use Kuxin\Loader;

/**
 * Class Exchange
 * @package App\Component\Exchange
 * @author  Pakey <pakey@qq.com>
 * @property \App\Component\Exchange\Provider\Binance $binance
 * @property \App\Component\Exchange\Provider\Huobi   $huobi
 */
class Exchange
{


    protected $apikey;

    protected $secret;

    public function __construct($apikey = '', $secret = '')
    {
        $this->apikey = $apikey;
        $this->secret = $secret;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $class = "\\App\\Component\\Exchange\\Provider\\" . ucfirst($name);
        if (class_exists($class)) {
            return Loader::instance($class, [$this->apikey, $this->secret]);
        } else {
            trigger_error('未定义的交易provider', E_USER_ERROR);
        }
    }
}