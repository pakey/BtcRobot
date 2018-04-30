<?php
include KX_ROOT . '/env.php';
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
            'driver' => 'memcached',
            'option' => [
                'host' => KX_CACHE_COMMON_HOST,
                'port' => KX_CACHE_COMMON_PORT,
            ],
        ],
    ],

    'database' => [
        'common' => [
            'driver' => 'mysql',
            'option' => [
                'host' => KX_DB_HOST,
                'port' => KX_DB_PORT,
                'user' => KX_DB_USER,
                'pwd'  => KX_DB_PWD,
                'name' => KX_DB_NAME,
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