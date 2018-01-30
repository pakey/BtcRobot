<?php

namespace App\Migrate;

use Kuxin\Db\Migrate;

class Kx_20180130103242_price extends Migrate
{
    // 执行修改
    public function up()
    {
        $this->create('price',function(){
            $this->addComand("`id` int(10) unsigned NOT NULL AUTO_INCREMENT");
            $this->addComand('`coin` char(10) not null comment "币种"');
            $this->addComand('`market` char(6) not null comment "市场"');
            $this->addComand('`time` bigint(14) unsigned comment "时间"');
            $this->addComand('`open` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`close` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`hign` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`low` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`volume` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`money` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`num` int unsigned default 0');
            $this->addComand('`buy_volume` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`buy_money` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`sell_volume` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`sell_money` DECIMAL(14,8) unsigned default 0');
            $this->addComand('`type` tinyint unsigned default 0 comment "1 buy 2 sell"');
            $this->addComand("PRIMARY KEY (`id`)");
            $this->addComand("KEY `coin_market` (`coin`,`market`)");
            $this->addComand("KEY `coin_time` (`coin`,`time`)");
        });
    }

    // 回滚修改
    public function down()
    {
        $this->drop('price');
    }
}