<?php

namespace App\Controller;

use App\Model\Price\Price;
use App\Model\Trade\Trade;
use Kuxin\Config;
use Kuxin\Controller;
use Kuxin\Helper\Http;
use Kuxin\Helper\Json;
use Kuxin\View;

class Index extends Controller
{

    public function index()
    {
        $bn=Json::decode(Http::get('https://api.binance.com/api/v3/ticker/price'));
        $aex=Json::decode(Http::get('https://api.aex.com/ticker.php?c=all&mk_type=btc'));
        $bn=array_column($bn,'price','symbol');
        $records=[];
        foreach($aex as $coin=>$item){
            $symbol=strtoupper($coin.'btc');
            if(isset($bn[$symbol])){
                $records[]=[
                    'coin'=>$coin,
                    'bn'=>$bn[$symbol],
                    'aex'=>$item['ticker']['last']*100000000,
                    'cha'=>round(($bn[$symbol]-$item['ticker']['last'])/$item['ticker']['last']*100,2)
                ];
            }
        }
        var_dump($records);
        exit;
    }

    public function down()
    {
        error_reporting(0);
        $start  = date('Ymd', time() - 7 * 86400);
        $today  = date('Ymd');
        $items  = Config::get('coin');
        $trade  = Trade::I();
        $result = [];
        $offset = 5 > date('w') ? (5 - date('w')) : (12 - date('w'));
        foreach ($items as $coin => $coinname) {
            $records       = $trade->table('trade_' . $coin)
                ->field('day,sum(money) money')
                ->group('day')
                ->where(['day' => ['>=', $start]])
                ->select();
            $result[$coin] = [];
            $records       = array_column($records, 'money', 'day');
            for ($i = 7; $i >= 0; $i--) {
                $day                 = date('Ymd', time() - $i * 86400);
                $result[$coin][$day] = isset($records[$day]) ? round($records[$day], 2) : 0;
            }
        }
        $keys = array_keys($result['btc']);
        foreach ($keys as $key) {
            $result['total'][$key] = array_sum(array_column($result, $key));
        }
        $table = $totaltable = '';
        $table .= '<table class="table table-bordered table-hover table-condensed detail">';
        $table .= '<tr>';
        $table .= '<th rowspan="2" colspan="2">币种</th><th colspan="9">近7日交易情况</th><th colspan="2">本期分红</th><th colspan="2">今日交易情况</th>';
        $table .= '</tr>';
        $table .= '<tr>';
        for ($i = 7; $i >= 1; $i--) {
            $table .= '<th>' . date('Y-m-d', time() - $i * 86400) . '</th>';
        }
        $table    .= '<th>合计交易额</th><th>合计手续费</th><th>交易额</th><th>手续费</th><th>交易额</th><th>手续费</th>';
        $table    .= '</tr>';
        $daytotal = [];
        foreach ($items as $coin => $coinname) {
            $table .= '<tr>';
            $table .= '<th>' . $coinname . '</th>';
            $table .= '<th>' . $coin . '</th>';
            $total = 0;
            for ($i = 7; $i >= 1; $i--) {
                $day = date('Ymd', time() - $i * 86400);
                if (isset($result[$coin][$day])) {
                    $table              .= '<td>' . $result[$coin][$day] . '</td>';
                    $total              += $result[$coin][$day];
                    $daytotal[$day]     += $result[$coin][$day];
                    $daytotal['money7'] += $result[$coin][$day];
                } else {
                    $table .= '<td>0.00</td>';
                }
            }
            $table            .= '<td>' . $total . '</td>';
            $fee              = $trade->calcFee($coin, $total);
            $daytotal['fee7'] += $fee;
            $table            .= '<td>' . $fee . '</td>';

            $weekmoney             = array_sum(array_slice($result[$coin], $offset));
            $weekfee               = $trade->calcFee($coin, $weekmoney);
            $daytotal['moneyweek'] += $weekmoney;
            $daytotal['feeweek']   += $weekfee;
            $table                 .= '<td>' . $weekmoney . '</td>';
            $table                 .= '<td>' . $weekfee . '</td>';
            if (isset($result[$coin][$today])) {
                $table             .= '<td>' . $result[$coin][$today] . '</td>';
                $todayfee          = $trade->calcFee($coin, $result[$coin][$today]);
                $table             .= '<td>' . $todayfee . '</td>';
                $daytotal['money'] += $result[$coin][$today];
                $daytotal['fee']   += $todayfee;
            } else {
                $table .= '<td>0.00</td>';
                $table .= '<td>0.00</td>';
            }
            $table .= '</tr>';
        }
        $table .= '<tr>';
        $table .= '<th colspan="2">合计</th>';
        for ($i = 7; $i >= 1; $i--) {
            $day   = date('Ymd', time() - $i * 86400);
            $table .= '<td>' . $daytotal[$day] . '</td>';
        }
        $table .= '<td>' . $daytotal['money7'] . '</td>';
        $table .= '<td>' . $daytotal['fee7'] . '</td>';
        $table .= '<td>' . $daytotal['moneyweek'] . '</td>';
        $table .= '<td>' . $daytotal['feeweek'] . '</td>';
        $table .= '<td>' . $daytotal['money'] . '</td>';
        $table .= '<td>' . $daytotal['fee'] . '</td>';
        $table .= '</tr>';
        $last  = Price::I()->table('price_tmc')->where('avg > 0')->order('id desc')->find();

        $fenhong7    = round($daytotal['fee7'] / 2, 6);
        $fenhong1    = round($daytotal['fee'] / 2, 6);
        $fenhong    = round($daytotal['feeweek'] / 2, 6);
        $perfenhong7 = round($fenhong7 / $last['totalcoin'], 6);
        $perfenhong1 = round($fenhong1 / $last['totalcoin'], 6);
        $perfenhong = round($fenhong / $last['totalcoin'], 6);
        $nianhua7    = round($perfenhong7 / 7 * 365 / $last['avg'], 4) * 100;
        $nianhua    = round($perfenhong / 7 * 365 / $last['avg'], 4) * 100;
        $nianhua1    = round($perfenhong1 * 365 / $last['avg'], 4) * 100;
        $table       .= '<tr><th colspan="2">分红</th><td colspan="8">&nbsp;</td><td>' . $fenhong7 . '</td><td>&nbsp;</td><td>' . $fenhong . '</td><td>&nbsp;</td><td>' . $fenhong1 . '</td></tr>';
        $table       .= '<tr><th colspan="2">每币分红</th><td colspan="8">&nbsp;</td><td>' . $perfenhong7 . '</td><td>&nbsp;</td><td>' . $perfenhong . '</td><td>&nbsp;</td><td>' . $perfenhong1 . '</td></tr>';
        $table       .= '<tr><th colspan="2">年化收益</th><td colspan="8">&nbsp;</td><td>' . $nianhua7 . '</td><td>&nbsp;</td><td>' . $nianhua . '</td><td>&nbsp;</td><td>' . $nianhua1 . '</td></tr>';
        $table       .= '</table>';
        $totaltable       .= '<table class="table table-bordered table-hover table-condensed total" style="margin:20px auto 30px;">';
        $totaltable       .= '<tr>
<th colspan="2">概况</th>
<th colspan="3">本期分红累计</th>
<th colspan="3">日分红概况</th>
<th colspan="2">周分红估值(前7日)</th>
</tr>';
        $totaltable       .= '<tr>
<th>持仓数</th><th>总币数</th>
<th>成交额</th><th>分红金额</th><th>年化收益</th>
<th>成交额</th><th>分红金额</th><th>年化收益</th>
<th>分红估值</th><th>年化收益</th>
</tr>';
        $totaltable       .= "<tr>
<td>{$last['usernum']}</td><td>{$last['totalcoin']}</td>
<td>{$daytotal['moneyweek']}</td><td>{$perfenhong}</td><td>{$nianhua}</td>
<td>{$daytotal['money']}</td><td>{$perfenhong1}</td><td>{$nianhua1}</td>
<td>{$perfenhong7}</td><td>{$nianhua7}</td>
</tr>";
        $totaltable       .= '</table>';
        return View::make('index',[
            'total'=>$totaltable,
            'detail'=>$table,
        ]);
    }
}