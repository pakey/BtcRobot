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
            $this->addComand('`exchange` char(10) not null comment "交易所"');
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
            $this->addComand('`change` DECIMAL(10,4) unsigned default 0');
            $this->addComand('`rsi6` DECIMAL(8,4) unsigned default 0');
            $this->addComand('`rsi12` DECIMAL(8,4) unsigned default 0');
            $this->addComand('`k` DECIMAL(8,4) unsigned default 0');
            $this->addComand('`d` DECIMAL(8,4) unsigned default 0');
            $this->addComand("PRIMARY KEY (`id`)");
            $this->addComand("KEY `exchange` (`exchange`)");
            $this->addComand("KEY `idx_exchange_coin_market_time` (`exchange`,`coin`,`market`,`time`)");
        });
    }

    // 回滚修改
    public function down()
    {
        $this->drop('price');
    }
}