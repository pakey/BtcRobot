<?php

function formular($quote_data, $formular)
{
    $json = json_decode($quote_data);

    //	echo $json->{"datas"}[0]->{"open"};
    $str = strtolower($formular);
    if($str == "kdj" )
        return kdj($json->{"datas"});
    else if($str == "macd")
        return macd($json->{"datas"});
    else if($str == "rsi")
        return rsi($json->{"datas"});
    else if($str == "bias")
        return bias($json->{"datas"});
    else if($str == "arbr")
        return arbr($json->{"datas"});

    else if($str == "cci")
        return cci($json->{"datas"});
    else if($str == "dmi")
        return dmi($json->{"datas"});

    else if($str == "cr")
        return cr($json->{"datas"});
    else if($str == "psy")
        return psy($json->{"datas"});
    else if($str == "kd")
        return kd($json->{"datas"});
    else if($str == "wr")
        return wr($json->{"datas"});
    else if($str == "trix")
        return trix($json->{"datas"});

    else
        return "";
}



function kdj($arr)
{
    /*
    RSV:=(CLOSE-LLV(LOW,N))/(HHV(HIGH,N)-LLV(LOW,N))*100;
K:SMA(RSV,M1,1);
D:SMA(K,M2,1);
J:3*K-2*D;*/
    $data = array();
    $count = count($arr);

    $N=14;
    $M1=3;
    $M2=3;

    for($i=0;$i<$count;$i++)
    {
        $s = max(0, $i-$N+1);
        $LOW = 100000;
        $HIGH = 0;
        for($j=$s;$j<=$i;$j++)
        {
            $LOW = min($LOW, $arr[$j]->{"low"});
            $HIGH = max($HIGH, $arr[$j]->{"high"});
        }
        $RSV[$i]=($arr[$i]->{"close"}-$LOW)/($HIGH-$LOW)*100;
    }

    $K = SMA($RSV, 0, $M1, 1);
    $D = SMA($K, $M1, $M2, 1);

    for ($i = 0; $i < $M1+$M2-1; $i++)
    {
        $J[$i] = -100000.0;
    }
    for ($i = $M1+$M2-1; $i < $count; $i++)
    {
        $J[$i] = 3*$K[$i] - 2*$D[$i];
    }
    $data['name']="随机指标";
    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="K";
    $data['lines'][0]['data']=$K;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="D";
    $data['lines'][1]['data']=$D;

    $data['lines'][2]['type']="line";
    $data['lines'][2]['name']="J";
    $data['lines'][2]['data']=$J;

    return $data;
}

function kd($arr)
{
    /*
    RSV:=(CLOSE-LLV(LOW,N))/(HHV(HIGH,N)-LLV(LOW,N))*100;
K:SMA(RSV,M1,1);
D:SMA(K,M2,1);
J:3*K-2*D;*/
    $data = array();
    $count = count($arr);

    $N=9;
    $M1=3;
    $M2=3;

    for($i=0;$i<$count;$i++)
    {
        $s = max(0, $i-$N+1);
        $LOW = 100000;
        $HIGH = 0;
        for($j=$s;$j<=$i;$j++)
        {
            $LOW = min($LOW, $arr[$j]->{"low"});
            $HIGH = max($HIGH, $arr[$j]->{"high"});
        }
        $RSV[$i]=($arr[$i]->{"close"}-$LOW)/($HIGH-$LOW)*100;
        var_dump( $RSV[$i]);
    }
    $K = SMA($RSV, 0, $M1, 1);
    $D = SMA($K, $M1, $M2, 1);

    $data['name']="随机指标";
    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="K";
    $data['lines'][0]['data']=$K;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="D";
    $data['lines'][1]['data']=$D;

    return $data;
}

function macd($arr)
{
    /*
    DIFF : EMA(CLOSE,SHORT) - EMA(CLOSE,LONG);
    DEA  : EMA(DIFF,M);
    MACD : 2*(DIFF-DEA), COLORSTICK;*/
    $data = array();
    $count = count($arr);

    $LONG=26;
    $SHORT=12;
    $M=9;

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $s = EMA($close, 0, $SHORT);
    $l = EMA($close, 0, $LONG);
    for($i=0;$i<$LONG;$i++)
        $DIFF[$i]= -100000.0;
    for($i=$LONG;$i<$count;$i++)
        $DIFF[$i]= $s[$i] - $l[$i];

    $DEA = EMA($DIFF, $LONG, $M);

    for ($i = 0; $i < $LONG+$M-1; $i++)
    {
        $MACD[$i] = -100000.0;
    }
    for ($i = $LONG+$M-1; $i < $count; $i++)
    {
        $MACD[$i] = 2*($DIFF[$i] - $DEA[$i]);
    }
    $data['name']="指数平滑异同移动平均线";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="DIFF";
    $data['lines'][0]['data']=$DIFF;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="DEA";
    $data['lines'][1]['data']=$DEA;

    $data['lines'][2]['type']="colorstick";
    $data['lines'][2]['name']="MACD";
    $data['lines'][2]['data']=$MACD;

    return json_encode($data);
}

function rsi($arr)
{
    /*
    LC := REF(CLOSE,1);
    RSI1:SMA(MAX(CLOSE-LC,0),N1,1)/SMA(ABS(CLOSE-LC),N1,1)*100;
    RSI2:SMA(MAX(CLOSE-LC,0),N2,1)/SMA(ABS(CLOSE-LC),N2,1)*100;
    RSI3:SMA(MAX(CLOSE-LC,0),N3,1)/SMA(ABS(CLOSE-LC),N3,1)*100;*/
    $data = array();
    $count = count($arr);

    $N1=6;
    $N2=12;
    $N3=24;

    $LC = array();
    $LC[0] = -100000.0;
    for($i=1;$i<$count;$i++)
        $LC[$i] = $arr[$i]->{"close"} - $arr[$i-1]->{"close"};

    $MAX = _MAX($LC, 0, 0);

    $ABS = array();
    $ABS[0] = -100000.0;
    for($i=1;$i<$count;$i++)
        $ABS[$i] = abs($LC[$i]);

    $MAX1 = SMA($MAX, 1, $N1, 1);
    $MAX2 = SMA($MAX, 1, $N2, 1);
    $MAX3 = SMA($MAX, 1, $N3, 1);

    $ABS1 = SMA($ABS, 1, $N1, 1);
    $ABS2 = SMA($ABS, 1, $N2, 1);
    $ABS3 = SMA($ABS, 1, $N3, 1);

    $bl = 1 + $N1 - 1;

    $RSI1 = array();
    for($i=0;$i<$bl;$i++)
        $RSI1[$i] = -100000.0;
    for($i=$bl;$i<$count;$i++)
        $RSI1[$i] = $MAX1[$i]*100.0/$ABS1[$i];

    $bl = 1 + $N2 - 1;

    $RSI2 = array();
    for($i=0;$i<$bl;$i++)
        $RSI2[$i] = -100000.0;
    for($i=$bl;$i<$count;$i++)
        $RSI2[$i] = $MAX2[$i]*100.0/$ABS2[$i];

    $bl = 1 + $N3 - 1;
    $RSI3 = array();
    for($i=0;$i<$bl;$i++)
        $RSI3[$i] = -100000.0;
    for($i=$bl;$i<$count;$i++)
        $RSI3[$i] = $MAX3[$i]*100.0/$ABS3[$i];

    $data['name']="相对强弱指标";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="RSI1";
    $data['lines'][0]['data']=$RSI1;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="RSI2";
    $data['lines'][1]['data']=$RSI2;

    $data['lines'][2]['type']="line";
    $data['lines'][2]['name']="RSI3";
    $data['lines'][2]['data']=$RSI3;

    return json_encode($data);
}

function bias($arr)
{
    /*
    BIAS1 : (CLOSE-MA(CLOSE,L1))/MA(CLOSE,L1)*100;
    BIAS2 : (CLOSE-MA(CLOSE,L2))/MA(CLOSE,L2)*100;
    BIAS3 : (CLOSE-MA(CLOSE,L3))/MA(CLOSE,L3)*100;*/
    $data = array();
    $count = count($arr);

    $N1=6;
    $N2=12;
    $N3=24;

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $MA1 = MA($close, 0, $N1);
    $MA2 = MA($close, 0, $N2);
    $MA3 = MA($close, 0, $N3);

    $bl = $N1 - 1;

    $BIAS1 = array();
    for($i=0;$i<$bl;$i++)
        $BIAS1[$i] = -100000.0;
    for($i=$bl;$i<$count;$i++)
        $BIAS1[$i] = ($close[$i] - $MA1[$i])*100.0/$MA1[$i];

    $bl = $N2 - 1;

    $BIAS2 = array();
    for($i=0;$i<$bl;$i++)
        $BIAS2[$i] = -100000.0;
    for($i=$bl;$i<$count;$i++)
        $BIAS2[$i] = ($close[$i] - $MA2[$i])*100.0/$MA2[$i];

    $bl = $N3 - 1;
    $BIAS3 = array();
    for($i=0;$i<$bl;$i++)
        $BIAS3[$i] = -100000.0;
    for($i=$bl;$i<$count;$i++)
        $BIAS3[$i] = ($close[$i] - $MA3[$i])*100.0/$MA3[$i];

    $data['name']="乖离率";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="BIAS1";
    $data['lines'][0]['data']=$BIAS1;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="BIAS2";
    $data['lines'][1]['data']=$BIAS2;

    $data['lines'][2]['type']="line";
    $data['lines'][2]['name']="BIAS3";
    $data['lines'][2]['data']=$BIAS3;

    return json_encode($data);
}

function arbr($arr)
{
    /*
    BIAS1 : (CLOSE-MA(CLOSE,L1))/MA(CLOSE,L1)*100;
    BIAS2 : (CLOSE-MA(CLOSE,L2))/MA(CLOSE,L2)*100;
    BIAS3 : (CLOSE-MA(CLOSE,L3))/MA(CLOSE,L3)*100;


    AR : SUM(HIGH-OPEN,N)/SUM(OPEN-LOW,N)*100;
    BR : SUM(MAX(0,HIGH-REF(CLOSE,1)),N)/SUM(MAX(0,REF(CLOSE,1)-LOW),N)*100
     */
    $data = array();
    $count = count($arr);

    $N=26;

    $high = array();
    for($i=0;$i<$count;$i++)
        $high[$i] = $arr[$i]->{"high"};

    $low = array();
    for($i=0;$i<$count;$i++)
        $low[$i] = $arr[$i]->{"low"};

    $open = array();
    for($i=0;$i<$count;$i++)
        $open[$i] = $arr[$i]->{"open"};

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $LC = array();
    $LC[0] = -100000.0;
    for($i=1;$i<$count;$i++)
        $LC[$i] = $arr[$i-1]->{"close"};

    $high_open = array();
    for($i=0;$i<$count;$i++)
        $high_open[$i] = $high[$i] - $open[$i];

    $open_low = array();
    for($i=0;$i<$count;$i++)
        $open_low[$i] = $open[$i] - $low[$i];

    $sum1 = SUM($high_open, 0, $N);
    $sum2 = SUM($open_low, 0, $N);

    $AR = array();
    for($i=0;$i<$count;$i++)
        $AR[$i] = $sum1[$i]*100/$sum2[$i];

    $high_LC = array();
    for($i=0;$i<$count;$i++)
        $high_LC[$i] = max(0, $high[$i] - $LC[$i]);

    $LC_low = array();
    for($i=0;$i<$count;$i++)
        $LC_low[$i] = max(0, $LC[$i] - $low[$i]);

    $sum1 = SUM($high_LC, 0, $N);
    $sum2 = SUM($LC_low, 0, $N);

    $BR = array();
    for($i=0;$i<$count;$i++)
        $BR[$i] = $sum1[$i]*100/$sum2[$i];


    $data['name']="人气意愿指标";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="AR";
    $data['lines'][0]['data']=$AR;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="BR";
    $data['lines'][1]['data']=$BR;

    return json_encode($data);
}

function cci($arr)
{
    /*
    TYP := (HIGH + LOW + CLOSE)/3;
    CCI:(TYP-MA(TYP,N))/(0.015*AVEDEV(TYP,N))
    */
    $data = array();
    $count = count($arr);

    $N=14;

    $bl = $N - 1;

    $high = array();
    for($i=0;$i<$count;$i++)
        $high[$i] = $arr[$i]->{"high"};

    $low = array();
    for($i=0;$i<$count;$i++)
        $low[$i] = $arr[$i]->{"low"};

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $typ = array();
    for($i=0;$i<$count;$i++)
        $typ[$i] = ($high[$i] + $low[$i] + $close[$i])/3;

    $_ma = MA($typ, 0, $N);
    $_avedev = AVEDEV($typ, 0, $N);

    $cc = array();
    for($i=0;$i<$bl;$i++)
        array_push($cc, -100000.0);
    for($i=$bl;$i<$count;$i++)
        array_push($cc, ($typ[$i] - $_ma[$i])/(0.015*$_avedev[$i]));

    $data['name']="顺势指标";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="CCI";
    $data['lines'][0]['data']=$cc;

    return json_encode($data);
}

function dmi($arr)
{
    /*
    TR := SUM(MAX(MAX(HIGH-LOW,ABS(HIGH-REF(CLOSE,1))),ABS(LOW-REF(CLOSE,1))),N);
    HD := HIGH-REF(HIGH,1);
    LD := REF(LOW,1)-LOW;
    DMP:= SUM(IF(HD>0 AND HD>LD,HD,0),N);
    DMM:= SUM(IF(LD>0 AND LD>HD,LD,0),N);
    PDI: DMP*100/TR;
    MDI: DMM*100/TR;
    ADX: MA(ABS(MDI-PDI)/(MDI+PDI)*100,M);
    ADXR:(ADX+REF(ADX,M))/2
    */
    $data = array();
    $count = count($arr);

    $N=14;
    $M=6;

    $high = array();
    for($i=0;$i<$count;$i++)
        $high[$i] = $arr[$i]->{"high"};

    $low = array();
    for($i=0;$i<$count;$i++)
        $low[$i] = $arr[$i]->{"low"};

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $LC = REF($close, 0, 1);
    $LH = REF($high, 0, 1);
    $LL = REF($low, 0, 1);

    $high_low = array();
    for($i=0;$i<$count;$i++)
        $high_low[$i] = $high[$i] - $low[$i];

    $high_LC = array();
    for($i=0;$i<$count;$i++)
        $high_LC[$i] = abs($high[$i] - $LC[$i]);

    $low_LC = array();
    for($i=0;$i<$count;$i++)
        $low_LC[$i] = abs($low[$i] - $LC[$i]);

    $max1 = _MAX($high_low, $high_LC, 1);
    $max2 = _MAX($max1, $low_LC, 1);
    $tr = SUM($max2, 1, $N);

    $hd = array();
    for($i=0;$i<$count;$i++)
        $hd[$i] = $high[$i] - $LH[$i];

    $ld = array();
    for($i=0;$i<$count;$i++)
        $ld[$i] = $LL[$i] - $low[$i];

    $dmp1 = array();
    for($i=0;$i<$count;$i++){
        if($hd[$i]>0 && $hd[$i]>$ld[$i])
            $dmp1[$i] = $hd[$i];
        else
            $dmp1[$i] = 0;
    }
    $dmp = SUM($dmp1, 1, $N);

    $dmm1 = array();
    for($i=0;$i<$count;$i++){
        if($ld[$i]>0 && $ld[$i]>$hd[$i])
            $dmm1[$i] = $ld[$i];
        else
            $dmm1[$i] = 0;
    }
    $dmm = SUM($dmm1, 1, $N);

    $pdi = array();
    for($i=0;$i<$count;$i++)
        $pdi[$i] = $dmp[$i]*100/$tr[$i];

    $mdi = array();
    for($i=0;$i<$count;$i++)
        $mdi[$i] = $dmm[$i]*100/$tr[$i];

    $mdi_pdi = array();
    for($i=0;$i<$count;$i++)
        $mdi_pdi[$i] = abs($mdi[$i] - $pdi[$i])*100/($pdi[$i] + $mdi[$i]);

    $adx = MA($mdi_pdi, 1, $M);

    $adx_l = REF($adx, 1, $M);

    $adxr = array();
    for($i=0;$i<$count;$i++)
        $adxr[$i] = ($adx[$i] + $adx_l[$i])/2;

    $data['name']="趋向指标";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="PDI";
    $data['lines'][0]['data']=$pdi;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="MDI";
    $data['lines'][1]['data']=$mdi;

    $data['lines'][2]['type']="line";
    $data['lines'][2]['name']="ADX";
    $data['lines'][2]['data']=$adx;

    $data['lines'][3]['type']="line";
    $data['lines'][3]['name']="ADXR";
    $data['lines'][3]['data']=$adxr;

    return json_encode($data);
}

function cr($arr)
{
    /*
    MID := (HIGH+LOW+CLOSE)/3;
    CR:SUM(MAX(0,HIGH-REF(MID,1)),N)/SUM(MAX(0,REF(MID,1)-L),N)*100;
    MA1:REF(MA(CR,M1),M1/2.5+1);
    MA2:REF(MA(CR,M2),M2/2.5+1);
    MA3:REF(MA(CR,M3),M3/2.5+1);
    */
    $data = array();
    $count = count($arr);

    $N=26;
    $M1=5;
    $M2=10;
    $M3=20;

    $high = array();
    for($i=0;$i<$count;$i++)
        $high[$i] = $arr[$i]->{"high"};

    $low = array();
    for($i=0;$i<$count;$i++)
        $low[$i] = $arr[$i]->{"low"};

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $mid = array();
    for($i=0;$i<$count;$i++)
        $mid[$i] = ($high[$i] + $low[$i] + $close[$i])/3;

    $mid_l = REF($mid, 0, 1);

    $high_mid = array();
    for($i=0;$i<$count;$i++)
        $high_mid[$i] = $high[$i] - $mid_l[$i];

    $mid_low = array();
    for($i=0;$i<$count;$i++)
        $mid_low[$i] = $mid_l[$i] - $low[$i];

    $max1 = _MAX(0, $high_mid, 1);
    $max2 = _MAX(0, $mid_low, 1);

    $sum1 = SUM($max1, 1, $N);
    $sum2 = SUM($max2, 1, $N);

    $cr = array();
    for($i=0;$i<$count;$i++)
        $cr[$i] = $sum1[$i]*100/$sum2[$i];

    $ma1 = MA($cr, 1, $M1);
    $ma2 = MA($cr, 1, $M2);
    $ma3 = MA($cr, 1, $M3);

    $ma1 = REF($ma1, 1, $M1/2.5 + 1);
    $ma2 = REF($ma2, 1, $M1/2.5 + 1);
    $ma3 = REF($ma3, 1, $M1/2.5 + 1);

    $data['name']="能量指标";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="CR";
    $data['lines'][0]['data']=$cr;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="MA1";
    $data['lines'][1]['data']=$ma1;

    $data['lines'][2]['type']="line";
    $data['lines'][2]['name']="MA2";
    $data['lines'][2]['data']=$ma2;

    $data['lines'][3]['type']="line";
    $data['lines'][3]['name']="MA3";
    $data['lines'][3]['data']=$ma3;

    return json_encode($data);
}

function psy($arr)
{
    /*
    PSY:COUNT(CLOSE>REF(CLOSE,1),N)/N*100
    */
    $data = array();
    $count = count($arr);

    $N=12;

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $LC = REF($close, 0, 1);

    $psy = array();
    for($i=0;$i<$N;$i++){
        array_push($psy, -100000.0);
    }

    for($i=$N;$i<$count;$i++){
        $ax = 0;
        for($j=$i-$N+1;$j<=$i;$j++){
            if($close[$j] > $LC[$j])
                $ax++;
        }
        array_push($psy, $ax*100/$N);
    }

    $data['name']="心理线";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="PSY";
    $data['lines'][0]['data']=$psy;

    return json_encode($data);
}

function wr($arr)
{
    /*
    WR:100*(HHV(HIGH,N)-CLOSE)/(HHV(HIGH,N)-LLV(LOW,N))
    */
    $data = array();
    $count = count($arr);

    $N=14;

    $high = array();
    for($i=0;$i<$count;$i++)
        $high[$i] = $arr[$i]->{"high"};

    $low = array();
    for($i=0;$i<$count;$i++)
        $low[$i] = $arr[$i]->{"low"};

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $hhv = HHV($high, 0, $N);
    $llv = LLV($low, 0, $N);

    $wr = array();
    for($i=0;$i<$N;$i++){
        array_push($wr, -100000.0);
    }

    for($i=$N;$i<$count;$i++){
        $ax = 100*($hhv[$i] - $close[$i])/($hhv[$i] - $llv[$i]);

        array_push($wr, $ax);
    }

    $data['name']="威廉指标";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="W&R";
    $data['lines'][0]['data']=$wr;

    return json_encode($data);
}
/*
	TR:= EMA(EMA(EMA(CLOSE,N),N),N);
	TRIX : (TR-REF(TR,1))/REF(TR,1)*100;
	TRMA :  MA(TRIX,M);
*/
function trix($arr)
{

    $data = array();
    $count = count($arr);

    $N=12;
    $M=20;

    $close = array();
    for($i=0;$i<$count;$i++)
        $close[$i] = $arr[$i]->{"close"};

    $tr = EMA($close, 0, $N);
    $tr = EMA($tr, $N, $N);
    $tr = EMA($tr, $N+$N, $N);

    $tr_l = REF($tr, $N+$N+$N, 1);

    $trix = array();
    for($i=0;$i<$N+$N+$N+1;$i++){
        array_push($trix, -100000.0);
    }
    for($i=$N+$N+$N+1;$i<$count;$i++){
        array_push($trix, ($tr[$i] - $tr_l[$i])*100/$tr_l[$i]);
    }

    $trma = MA($trix, $N+$N+$N+1, $M);

    $data['name']="三重指数平滑平均线";

    $data['lines'][0]['type']="line";
    $data['lines'][0]['name']="TRIX";
    $data['lines'][0]['data']=$trix;

    $data['lines'][1]['type']="line";
    $data['lines'][1]['name']="TRMA";
    $data['lines'][1]['data']=$trma;

    return json_encode($data);
}


function SMA($arr, $b, $N, $M)
{
    $bl = $b + $N-1;
    $be = count($arr)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    $db = 0;
    for ($j = $bl - $N + 1; $j <= $bl; $j++)
    {
        $db += $arr[$j];
    }
    $db /= $N;
    array_push($data, $db);

    for ($i = $bl + 1; $i <= $be; $i++)
    {
        //求移动平均。
        //	用法:
        //	SMA(X,N,M),求X的N日移动平均，M为权重。
        //	算法: 若Y=SMA(X,N,M)
        //	则 Y=[M*X+(N-M)*Y')/N,其中Y'表示上一周期Y值,N必须大于M。
        //	例如：SMA(CLOSE,30,1)表示求30日移动平均价
        $db = ($M*$arr[$i] + ($N - $M)*$db) / $N;

        array_push($data, $db);
    }
    return $data;
}

function EMA($arr, $b, $N)
{
    $bl = $b + $N-1;
    $be = count($arr)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    array_push($data, $arr[$bl]);

    $db = $data[$bl];
    for ($i = $bl + 1; $i <= $be; $i++)
    {
        //求移动平均。
        //	用法:
        //	EMA(X,N,M),求X的N日移动平均，M为权重。
        //	算法: 若Y=EMA(X,N)
        //	则 Y=(X-Y')*2.0/(N+1) + Y',其中Y'表示上一周期Y值,N必须大于M。
        //	例如：EMA(CLOSE,30)表示求30日移动平均价
        $db = ($arr[$i] - $db)*2.0 / ($N+1) + $db;

        array_push($data, $db);
    }
    return $data;
}

function MA($arr, $b, $N)
{
    $bl = $b + $N-1;
    $be = count($arr)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    for ($i = $bl; $i <= $be; $i++)
    {
        $ax = 0;

        for ($j = $i - $N + 1; $j <= $i; $j++)
            $ax += $arr[$j];

        $ax /= $N;

        array_push($data, $ax);
    }
    return $data;
}

function REF($arr, $b, $N)
{
    $bl = $b + $N;
    $be = count($arr)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    for ($i = $bl; $i <= $be; $i++)
    {
        array_push($data, $arr[$i - $N]);
    }
    return $data;
}

function SUM($arr, $b, $N)
{
    $bl = $b + $N-1;
    $be = count($arr)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    for ($i = $bl; $i <= $be; $i++)
    {
        $ax = 0;

        for ($j = $i - $N + 1; $j <= $i; $j++)
            $ax += $arr[$j];

        array_push($data, $ax);
    }
    return $data;
}

function AVEDEV($arr, $b, $N)
{
    $bl = $b + $N-1;
    $be = count($arr)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    for ($i = $bl; $i <= $be; $i++)
    {
        $ax = 0;
        $x = 0;

        for ($j = $i - $N + 1; $j <= $i; $j++)
            $ax += $arr[$j];

        $ax /= $N;

        for ($j = $i - $N + 1; $j <= $i; $j++)
            $x += abs($arr[$j] - $ax);

        $x /= $N;

        array_push($data, $x);
    }
    return $data;
}

function _MIN($arr, $arr2, $b)
{
    $bl = $b;
    $be = 0;
    $is_arr1 = is_array($arr);
    $is_arr2 = is_array($arr2);
    if($is_arr1)
        $be = count($arr)-1;
    else
        $be = count($arr2)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    for ($i = $bl; $i <= $be; $i++)
    {
        $ax = min($is_arr1 ? $arr[$i] : $arr, $is_arr2 ? $arr2[$i] : $arr2);

        array_push($data, $ax);
    }
    return $data;
}

function _MAX($arr, $arr2, $b)
{
    $bl = $b;
    $be = 0;
    $is_arr1 = is_array($arr);
    $is_arr2 = is_array($arr2);
    if($is_arr1)
        $be = count($arr)-1;
    else
        $be = count($arr2)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    for ($i = $bl; $i <= $be; $i++)
    {
        $ax = max($is_arr1 ? $arr[$i] : $arr, $is_arr2 ? $arr2[$i] : $arr2);

        array_push($data, $ax);
    }
    return $data;
}

function HHV($arr, $b, $N)
{
    $bl = $b + $N -1;
    $be = count($arr)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    for ($i = $bl; $i <= $be; $i++)
    {
        $ax = 0;

        for ($j = $i - $N + 1; $j <= $i; $j++)
            $ax = max($ax, $arr[$j]);

        array_push($data, $ax);
    }
    return $data;
}

function LLV($arr, $b, $N)
{
    $bl = $b + $N -1;
    $be = count($arr)-1;

    $data = array();
    for($i=0;$i<$bl;$i++)
        array_push($data, -100000.0);

    for ($i = $bl; $i <= $be; $i++)
    {
        $ax = 1000000;

        for ($j = $i - $N + 1; $j <= $i; $j++)
            $ax = min($ax, $arr[$j]);

        array_push($data, $ax);
    }
    return $data;
}
