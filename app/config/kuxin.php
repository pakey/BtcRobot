<?php
return [
    'app' => [
        'debug' => true,
        'mode'  => PHP_SAPI == 'cli' ? 'cli' : 'web',
        'env'   => '',
    ],

    'rewrite' => [
        'power' => 0,
    ],

    'log' => [
        'power'     => true,
        'buildtype' => ['kx', 'debug', 'console'],
    ],

    'view' => [
        'driver' => 'mc',
    ],

    'cache' => [
        'prefix' => 'kx_',
        'common' => [
            'driver' => 'memcache',
            'option' => [
                'host' => '127.0.0.1',
                'port' => '11211',
            ],
        ],
        'redis'  => [
            'driver' => 'redis',
            'option' => [
                'host'     => '127.0.0.1',
                'port'     => '6379',
                'password' => null,
                'database' => 0,
            ],
        ],
    ],

    'database' => [
        //        'prefix' => 'ptcms_',
        'common' => [
            'driver' => 'mysql',
            'option' => [
                'host' => '127.0.0.1',
                'port' => '3306',
                'user' => 'root',
                'pwd'  => 'root',
                'name' => 'btc38',
            ],
        ],
    ],

    'storage' => [
        'runtime'  => [
            'driver' => 'file',
            'option' => [
                'path' => KX_ROOT . '/storage/runtime',
            ],
        ],
        'log'      => [
            'driver' => 'file',
            'option' => [
                'path' => KX_ROOT . '/storage/log',
            ],
        ],
        'template' => [
            'driver' => 'file',
            'option' => [
                'path' => KX_ROOT . '/storage/template',
            ],
        ],
    ],

    'coookie' => [
        'prefix'   => 'PTCMS_',
        // cookie 保存时间
        'expire'   => 2592000,
        // cookie 保存路径
        'path'     => '/',
        // cookie 有效域名
        'domain'   => '',
        //  cookie 启用安全传输
        'secure'   => false,
        // httponly设置
        'httponly' => '',
    ],

    'session' => [
        'handler' => '',
        'path'    => '',
        'host'    => '',
        'port'    => '',
    ],

    'pinyin' => [
        'ucfirst' => 1,
    ],

    'phppath' => '/usr/local/bin/php',


    'coin' => [
        "btc"  => "比特币",
        "ltc"  => "莱特币",
        "doge" => "狗狗币",
        "xrp"  => "瑞波币",
        "bts"  => "比特股",
        "xlm"  => "恒星币",
        "nxt"  => "未来币",
        "ardr" => "阿朵",
        "blk"  => "黑币",
        "xem"  => "新经币",
        "emc"  => "崛起币",
        "dash" => "达世币",
        "xzc"  => "零币",
        "sys"  => "系统币",
        "vash" => "微币",
        "eac"  => "地球币",
        "xcn"  => "氪石币",
        "ppc"  => "点点币",
        "mgc"  => "众合币",
        "hlb"  => "活力币",
        "zcc"  => "招财币",
        "xpm"  => "质数币",
        "ncs"  => "资产股",
        "ybc"  => "元宝币",
        "anc"  => "阿侬币",
        "bost" => "增长币",
        "mec"  => "美卡币",
        "wdc"  => "世界币",
        "qrk"  => "夸克币",
        "dgc"  => "数码币",
        "bec"  => "比奥币",
        "ric"  => "黎曼币",
        "src"  => "安全币",
        "tag"  => "悬赏币",
        "med"  => "地中海币",
        "tmc"  => "时代币",
        "etc"  => "以太经典",
        "eth"  => "以太币",
    ],
];