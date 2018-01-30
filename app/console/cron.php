<?php


namespace App\Console;

use Kuxin\Config;
use Kuxin\Console;
use Kuxin\DI;

class Cron extends Console
{

    public function master()
    {
        $items = Config::get('coin');
        $tasks = [
            'trade',
//            'book',
            'price',
        ];
        while (true) {
            foreach ($items as $coin => $coinname) {
                foreach ($tasks as $task) {
                    if (in_array($task,['price','book'])  && $coin != 'tmc') {
                        continue;
                    }
                    $date = DI::Storage('log')->read($coin . '_' . $task);
                    if (!$date || strtotime($date) + 60 < time()) {
                        $param = [KX_ROOT . '/kx', 'task:' . $task, "coin/{$coin}/name/{$coinname}"];
                        echo date('Y-m-d H:i:s') . " 执行任务:  /usr/bin/php " . implode(' ', $param), PHP_EOL;
                        //启动
                        $process = new \swoole_process(function (\swoole_process $worker) use ($param) {
                            $worker->exec('/usr/bin/php', $param);
                        }, true);
                        $process->start();
                    }
                    $ret = \swoole_process::wait(false);
                }
            }
            sleep(5);
        }
    }
}