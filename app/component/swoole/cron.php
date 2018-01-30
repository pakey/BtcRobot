<?php

namespace App\Component\Swoole;

use Kuxin\Config;

class Cron
{

    public static function setTitle($name)
    {
        if (PHP_OS != 'Darwin') {
            //非苹果
            if (function_exists('swoole_set_process_name')) {
                swoole_set_process_name($name);
            } else {
                cli_set_process_title($name);
            }
        }
    }
}