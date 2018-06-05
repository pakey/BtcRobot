<?php

namespace App\Migrate;

use Kuxin\Db\Migrate;

class Kx_20180531215533_create_orders extends Migrate
{
    // 执行修改
    public function up()
    {
        $this->create('orders',function(){
            $this->addComand("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
            $this->addComand("PRIMARY KEY (`id`)");
        });
    }

    // 回滚修改
    public function down()
    {
        $this->drop('orders');
    }
}