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


    'phppath' => '/usr/bin/php',

];