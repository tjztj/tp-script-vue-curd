<?php



/**
 * static 防止重复访问数据库 获取登录人员信息
 * @return []
 */
function staticTpScriptVueCurdGetLoginData():array{
    static $data;
    if(!$data){
        $data=tpScriptVueCurdGetLoginData();
    }
    return $data;
}

/**
 * 递归合并两个数组
 * @param array $old
 * @param array $new
 * @return array
 */
function vueCurdMergeArrays(array $old,array $new):array{
    foreach ($old as $k=>$v){
        if(isset($new[$k])){
            if(is_array($v)&&is_array($new[$k])){
                $v=vueCurdMergeArrays($v,$new[$k]);
            }else{
                $v=$new[$k];
            }
            $old[$k]=$v;
        }
    }
    foreach ($new as $k=>$v){
        if(!isset($old[$k])){
            $old[$k]=$v;
        }
    }
    return $old;
}


/**
 * 获取最后一步
 * @param array|string|\tpScriptVueCurd\base\model\VueCurlModel $stepOrInfo
 * @return array|null
 */
function endStep($stepOrInfo): ?array
{
    if(empty($stepOrInfo)){
        return null;
    }

    if(is_array($stepOrInfo)){
        return end($stepOrInfo);
    }

    if(is_string($stepOrInfo)){
        return endStep(json_decode($stepOrInfo,true));
    }

    if($stepOrInfo instanceof \tpScriptVueCurd\base\model\VueCurlModel){
        return endStep($stepOrInfo[$stepOrInfo::getStepField()]);
    }

    return null;
}

/**
 * 判断对象数据库存的当前步骤是否是 $step
 * @param string|\tpScriptVueCurd\option\FieldStep $step
 * @param  array|string|\tpScriptVueCurd\base\model\VueCurlModel $stepOrInfo
 * @return bool
 */
function eqEndStep($step,$stepOrInfo): bool
{
    if($step instanceof \tpScriptVueCurd\option\FieldStep){
        $step=$step->getStep();
    }
    $endStep=endStep($stepOrInfo);
    if(!$endStep){
        return false;
    }
    return $step===$endStep['step'];
}

/**
 * 判断对象数据库存的步骤是否已经过了 $step，会自动去掉退回的一些步骤
 * @param $step
 * @param $stepOrInfo
 * @return bool
 */
function stepPast($step,$stepOrInfo):bool{
    if(is_string($stepOrInfo)){
        $stepHistory=json_decode($stepOrInfo,true);
    }else if($stepOrInfo instanceof \tpScriptVueCurd\base\model\VueCurlModel){
        $stepHistory=json_decode($stepOrInfo[$stepOrInfo::getStepField()],true);
    }else if(is_array($stepOrInfo)){
        $stepHistory=$stepOrInfo;
    }else{
        return false;
    }

    if($step instanceof \tpScriptVueCurd\option\FieldStep){
        $step=$step->getStep();
    }

    $stepValues=[];
    foreach ($stepHistory as $v){
        if(empty($v['back'])){
            $stepValues[]=(string)$v['step'];
        }else{
            $stepValues=array_slice($stepValues,0,count($stepValues)-$v['back']);
        }
    }
    return in_array((string)$step,$stepValues,true);
}

if (!function_exists('create_guid')) {
    function create_guid(): string
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            . substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12)
            . chr(125);// "}"
        return $uuid;
    }
}