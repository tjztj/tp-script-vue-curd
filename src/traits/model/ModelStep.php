<?php


namespace tpScriptVueCurd\traits\model;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\option\FieldStep;

trait ModelStep
{
    /**
     * 步奏字段
     * @return string
     */
    public static function getStepField():string{
        return getModelDefaultStepField();
    }


    /**
     * 获取要撤回的新的状态 $info->getBackStepData($step)  ;保存到数据库的操作需自己实现
     * @param FieldStep $newStep
     * @param BaseModel $baseInfo 如果有父表，需要传入父表的
     * @return array
     * @throws \think\Exception
     */
    public function getBackStepData(FieldStep $newStep,BaseModel $baseInfo=null){
        $nowStepInfo=$this->fields()->getCurrentStepInfo($this,$baseInfo);
        if(empty($nowStepInfo)){
            throw new \think\Exception('当前数据无步骤，不可撤回');
        }

        $backStep=$newStep->getStep();
        $nowStep=$nowStepInfo->getStep();
        if($backStep===$nowStep){
            throw new \think\Exception('数据已是撤回步骤，无需撤回');
        }


        $oldSteps=$this[self::getStepField()];
        if($oldSteps){
            $oldSteps=is_string($oldSteps)?json_decode($oldSteps,true):$oldSteps;
        }
        if(empty($oldSteps)){
            throw new \think\Exception('数据无法撤回，未检测到步骤记录');
        }

        $stepValues=[];
        foreach ($oldSteps as $v){
            if(empty($v['back'])&&!in_array($v['step'],$stepValues)){
                $stepValues[]=$v['step'];
            }
        }

        $backIndex= array_search($backStep, $stepValues, true);
        if($backIndex===false){
            throw new \think\Exception('记录中未找到要撤回到的步骤');
        }
        $nowIndex= array_search($nowStep, $stepValues, true);
        if($backIndex===false){
            throw new \think\Exception('记录中未找到当前步骤记录');
        }

        if($backIndex>$nowIndex){
            throw new \think\Exception('要撤回的步骤在当前步骤后面');
        }


        $oldSteps[]=[
            'step'=>$backStep,
            'time'=>time(),
            'user'=>staticTpScriptVueCurdGetLoginData()['id'],
            'back'=>$nowIndex-$backIndex,//后退步数
        ];

        return $oldSteps;
    }
}