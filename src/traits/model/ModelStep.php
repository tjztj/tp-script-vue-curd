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
     * 是否有步骤字段
     * @return bool
     */
    public static function hasStepField():bool{
        $field=static::getStepField();
        return $field&&in_array($field,static::getTableFields(),true);
    }

    /**
     * 下一步步骤字段
     * @return string
     */
    public static function getNextStepField():string{
        return getModelDefaultNextStepField();
    }


    /**
     * 是否有下一步步骤字段
     * @return bool
     */
    public static function hasNextStepField():bool{
        $field=static::getNextStepField();
        return $field&&in_array($field,static::getTableFields(),true);
    }

    /**
     * 当前字段步骤名称
     * @return string
     */
    public static function getCurrentStepField():string{
        return getModelDefaultCurrentStepField();
    }

    /**
     * 是否有当前步骤字段
     * @return bool
     */
    public static function hasCurrentStepField():bool{
        $field=static::getCurrentStepField();
        return $field&&in_array($field,static::getTableFields(),true);
    }

    /**
     * 数据当前流程已走步骤名称集合
     * @return string
     */
    public static function getStepPastsField():string{
        return getModelDefaultStepPastsField();
    }


    /**
     * 是否有当前数据已走流程的字段
     * @return bool
     */
    public static function hasStepPastsField():bool{
        $field=static::getStepPastsField();
        return $field&&in_array($field,static::getTableFields(),true);
    }


    /**
     * 获取并设置新的步骤信息
     * 使用：$info->newStepSaveData($newStep)
     * @todo 需要自己实现对 nextStep 下一步 的赋值
     * @param FieldStep $newStep
     * @return array
     * @throws \think\Exception
     */
    public function newStepSaveData(FieldStep $newStep):array{
        if(!static::hasStepField()){
            throw new \think\Exception('未设置步骤字段');
        }
        $data=[];

        //为了防止赋值错误，修正为正确的步骤的值，主要是back
        $data[static::getStepField()]=FieldStep::correctSteps($newStep->getNewStepJson($this[static::getStepField()]));
        $this[static::getStepField()]=$data[static::getStepField()];

        if(static::hasCurrentStepField()){
            $data[static::getCurrentStepField()]=endStepVal($data[static::getStepField()]);
            $this[static::getCurrentStepField()]=$data[static::getCurrentStepField()];
        }
        if(static::hasStepPastsField()){
            $pasts=getStepPasts($data[static::getStepField()]);
            if(is_null($pasts)){
                throw new \think\Exception('获取数据执行过步骤错误');
            }
            $data[static::getStepPastsField()]=implode(',',$pasts);
            $this[static::getStepPastsField()]=$data[static::getStepPastsField()];
        }
        return $data;
    }


    /**
     * 使用 $info->getBackStepData($newStep,$parentInfo)
     * @param FieldStep $newStep
     * @param BaseModel|null $parentInfo 如果有父表，需要传入父表的
     * @return array
     * @throws \think\Exception
     * @todo 步骤撤回的时候，删掉撤回的步骤的字段的值，不然统计可能出问题！！！  setSaveToNull，字段类型需设置
     * 获取要撤回的新的状态 $info->getBackStepData($step)  ;保存到数据库的操作需自己实现
     */
    public function getBackStepData(FieldStep $newStep,BaseModel $parentInfo=null): array
    {
        $nowStepInfo=$this->fields()->getCurrentStepInfo($this,$parentInfo);
        if($nowStepInfo === null){
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
            if(empty($v['back'])){
                $stepValues[]=$v['step'];
            }else{
                $stepValues=array_slice($stepValues,0,count($stepValues)-$v['back']);
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