<?php
/**
 * 写上要监控的目标站小说id 用竖线分隔
 * 文件放到任何位置,复制一份站点的规则 修改列表规则
 * 地址为本页地址,规则按照本页的代码进行制作,然后加入后台监控设置相应时间即可
 */
$novelids='1|2|3|4|5|6';

$novelids=explode('|',$novelids);

foreach($novelids as $novelid){
    echo "<novelid>{$novelid}</novelid><novelname>{$novelid}</novelname><updateid>{$_SERVER['REQUEST_TIME']}</updateid>".PHP_EOL;
}