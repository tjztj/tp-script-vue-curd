<?php

use think\facade\Env;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\option\ConfirmException;
use tpScriptVueCurd\tool\ErrorCode;


/**
 * static 防止重复访问数据库 获取登录人员信息
 * @return array []
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
 * 获取最后一步的详细信息
 * @param array|string|BaseModel $stepOrInfo
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

    if($stepOrInfo instanceof \tpScriptVueCurd\base\model\BaseModel){
        if(!$stepOrInfo::hasStepField()){
            return null;
        }

        return endStep($stepOrInfo[$stepOrInfo::getStepField()]);
    }

    return null;
}


/**
 * 获取最后一步的步骤的值
 * @param $stepOrInfo
 * @return string|null
 */
function endStepVal($stepOrInfo):?string{
    if(empty($stepOrInfo)&&$stepOrInfo!==0&&$stepOrInfo!=='0'){
        return null;
    }

    $endStepInfoVal=function($val){
        $endStep=endStep($val);
        if(is_null($endStep)){
            return null;
        }
        return $endStep['step'];
    };

    if(is_string($stepOrInfo)){
        $json=json_decode($stepOrInfo,true);
        if($json===null){
            return $stepOrInfo;
        }
        return $endStepInfoVal($json);
    }

    if(is_array($stepOrInfo)){
        return $endStepInfoVal($stepOrInfo);
    }

    if($stepOrInfo instanceof \tpScriptVueCurd\base\model\BaseModel){
        if($stepOrInfo::hasCurrentStepField()&&$stepOrInfo[$stepOrInfo::getCurrentStepField()]!=='') {
            return $stepOrInfo[$stepOrInfo::getCurrentStepField()];
        }
        if($stepOrInfo::hasStepPastsField()&&$stepOrInfo[$stepOrInfo::getStepPastsField()]!=='') {
            $steps=explode(',',$stepOrInfo[$stepOrInfo::getStepPastsField()]);
            return end($steps);
        }
        if($stepOrInfo::hasStepField()){
            return $endStepInfoVal($stepOrInfo[$stepOrInfo::getStepField()]);
        }
    }

    return null;
}

/**
 * 判断对象数据库存的当前步骤是否是 $step
 * @param string|\tpScriptVueCurd\option\FieldStep $step
 * @param  array|string|BaseModel $stepOrInfo
 * @return bool
 */
function eqEndStep($step,$stepOrInfo): bool
{
    if($step instanceof \tpScriptVueCurd\option\FieldStep){
        $step=$step->getStep();
    }
    $endStep=endStepVal($stepOrInfo);
    if($endStep===null){
        return false;
    }
    return $step===$endStep;
}

/**
 * 获取数据所有已完成的步骤，已退回的步骤不算在里面
 * @param string|BaseModel|array $stepOrInfo
 * @return array
 */
function getStepPasts($stepOrInfo):array{
    if(is_string($stepOrInfo)){
        $stepHistory=json_decode($stepOrInfo,true);
    }else if($stepOrInfo instanceof BaseModel){
        $stepHistory=json_decode($stepOrInfo[$stepOrInfo::getStepField()],true);
    }else if(is_array($stepOrInfo)){
        $stepHistory=$stepOrInfo;
    }else{
        return [];
    }

    $stepValues=[];
    if($stepHistory)foreach ($stepHistory as $v){
        if(empty($v['back'])){
            $stepValues[]=(string)$v['step'];
        }else{
            $stepValues=array_slice($stepValues,0,count($stepValues)-$v['back']);
        }
    }
    return $stepValues;
}

/**
 * 判断对象数据库存的步骤是否已经过了 $step，会自动去掉退回的一些步骤
 * @param string|\tpScriptVueCurd\option\FieldStep $step
 * @param string|BaseModel|array $stepOrInfo
 * @return bool
 */
function stepPast($step,$stepOrInfo):bool{
    if($step instanceof \tpScriptVueCurd\option\FieldStep){
        $step=$step->getStep();
    }
    return in_array((string)$step,getStepPasts($stepOrInfo),true);
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

/**
 * 框架根目录地址
 * @return string
 */
function getVCurdDir():string{
    return dirname(__DIR__).DIRECTORY_SEPARATOR;
}

/**
 * 是否开启了debug
 * @return bool
 */
function appIsDebug():bool{
    return \think\facade\App::isDebug();
}

/**
 * 报错时，是否直接抛出异常
 * @throws Exception
 */
function errorShowThrow(\Exception $error=null):void{
    if(is_null($error)){
        return;
    }
    if($error instanceof ConfirmException||in_array((int)$error->getCode(),ErrorCode::DEBUG_NOT_OUT_ERR_INFOS,true)){
        return;
    }
    $classPath=get_class($error);
    if(in_array($classPath,['think\Exception','Exception'])){
        return;
    }
    throw $error;
}