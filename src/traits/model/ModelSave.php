<?php


namespace tpScriptVueCurd\traits\model;



use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;

/**
 * Trait ModelSave
 * @package tpScriptVueCurd\traits\model
 * @author tj 1079798840@qq.com
 */
trait ModelSave
{

    /**
     * 添加基本事项
     * @param array $postData
     * @param BaseModel|null $parentInfo
     * @param FieldCollection $fields
     * @param bool $isExcelDo 是否excel操作
     * @param array $returnSaveData
     * @return $this
     * @throws \think\Exception
     */
    public function addInfo(array $postData,?BaseModel $parentInfo,FieldCollection $fields,bool $isExcelDo=false,array &$returnSaveData=[]):self{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################
        

        if(!$this->checkRowAuth($fields,$parentInfo,'add')){
            throw new \think\Exception('不能添加此栏目信息');
        }

        FieldDo::doSaveBefore($fields,$postData,$this,$parentInfo);

        //为了防止在doSaveData中被删除，在这里先获取了
        $saveStepInfo=$fields->saveStepInfo??null;

        $data=$this->doSaveData($postData,$this,$fields,$isExcelDo,$parentInfo,$saveFields);

        FieldDo::doSaveBeforeChecked($saveFields,$data,$this,$parentInfo);



        //没有设置当前步骤， excel导入不分步骤
        $haveDoStep=false;
        if(!$isExcelDo&&$fields->stepIsEnable()){
            //如果启用了步骤
            if(!static::hasStepField()){
                throw new \think\Exception('未设置步骤字段');
            }
            if(!isset($data[static::getStepField()])){
                if(!$saveStepInfo){
                    throw new \think\Exception('未能获取到当前步骤信息');
                }
                $haveDoStep=true;
                $saveStepInfo->doSaveBefore($data,$this,$parentInfo,$fields);
                //doSaveBefore中可能更改了步骤
                isset($data[static::getStepField()])||$data[static::getStepField()]=$saveStepInfo->getNewStepJson(null);
            }
        }

        if(isset($data[static::getStepField()])){
            //为了防止赋值错误，修正为正确的步骤的值，主要是back
            $data[static::getStepField()]=FieldStep::correctSteps($data[static::getStepField()]);
            if(static::hasCurrentStepField()){
                $data[static::getCurrentStepField()]=endStepVal($data[static::getStepField()]);
            }
            if(static::hasStepPastsField()){
                $pasts=getStepPasts($data[static::getStepField()]);
                if(is_null($pasts)){
                    throw new \think\Exception('获取数据执行过步骤错误');
                }
                $data[static::getStepPastsField()]=implode(',',$pasts);
            }
        }



        if($parentInfo){
            $data[static::parentField()]=$parentInfo->id;
//            $allFields=static::getTableFields();
        }

        if(static::getCreateLoginUserField()){
            $data[static::getCreateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        if(static::getUpdateLoginUserField()){
            $data[static::getUpdateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        if(isset($data[$this->createTime])&&empty($data[$this->createTime])){
            $data[$this->createTime]=time();
        }
        //onAddBefore请用doSaveDataAfter
        $info=clone $this;
        $info->replace(false)->save($data);

        FieldDo::doSaveAfter($saveFields,$data,$this,$info,$parentInfo);
        $this->onAddAfter($info,$data,$parentInfo);
        if($haveDoStep&&$saveStepInfo){
            $saveStepInfo->doSaveAfter($this,$info,$parentInfo,$fields,$data);
            if(static::hasNextStepField()){
                $nestStep=$this->fields()->getNextStepInfo($info,$parentInfo);
                $info[static::getNextStepField()]=$nestStep===null?'':$nestStep->getStep();
            }
        }
        $info->save();

        $returnSaveData=$data;
        return $info;
    }

    /**
     * 修改数据
     * @param array $postData 要保存的数据
     * @param FieldCollection $fields
     * @param BaseModel|null $parentInfo
     * @param BaseModel $beforeInfo
     * @param array $returnSaveData
     * @return $this
     * @throws \think\Exception
     */
    public function saveInfo(array $postData,FieldCollection $fields,?BaseModel $parentInfo,BaseModel $beforeInfo,array &$returnSaveData=[]): self
    {
        if($beforeInfo&&!empty($beforeInfo->id)&&!$beforeInfo->checkRowAuth($fields,$parentInfo,'edit')){
            throw new \think\Exception('您不能修改当前数据信息');
        }
        FieldDo::doSaveBefore($fields,$postData,$beforeInfo,$parentInfo);

        //为了防止在doSaveData中被删除，在这里先获取了
        $saveStepInfo=$fields->saveStepInfo??null;

        $data=$this->doSaveData($postData,$beforeInfo,$fields,$this->isExcelDo??false,$parentInfo,$saveFields);

        if(empty($postData['id'])){
            throw new \think\Exception('缺少ID');
        }

        $data['id']=$postData['id'];

        FieldDo::doSaveBeforeChecked($saveFields,$data,$beforeInfo,$parentInfo);


        $haveDoStep=false;
        if($fields->stepIsEnable()){
            //如果启用了步骤
            if(!static::hasStepField()){
                throw new \think\Exception('未设置步骤字段');
            }
            //没有设置当前步骤
            if(!isset($data[static::getStepField()])){
                if(!$saveStepInfo){
                    throw new \think\Exception('未能获取到当前步骤信息');
                }
                $haveDoStep=true;
                $saveStepInfo->doSaveBefore($data,$beforeInfo,$parentInfo,$fields);
                isset($data[static::getStepField()])||$data[static::getStepField()]=$saveStepInfo->getNewStepJson($beforeInfo[static::getStepField()]);
            }
        }

        if(isset($data[static::getStepField()])){
            //为了防止赋值错误，修正为正确的步骤的值，主要是back
            $data[static::getStepField()]=FieldStep::correctSteps($data[static::getStepField()]);
            if(static::hasCurrentStepField()){
                $data[static::getCurrentStepField()]=endStepVal($data[static::getStepField()]);
            }
            if(static::hasStepPastsField()){
                $pasts=getStepPasts($data[static::getStepField()]);
                if(is_null($pasts)){
                    throw new \think\Exception('获取数据执行过步骤错误');
                }
                $data[static::getStepPastsField()]=implode(',',$pasts);
            }
        }

        foreach (['create_time','update_time','delete_time'] as $v){
            try{
                $fields->getFieldByNmae($v);
                if(!empty($beforeInfo->readonly))unset($beforeInfo->readonly[array_search($v,$beforeInfo->readonly)]);
            }catch (\Exception $e){
                unset($data[$v]);
            }
        }
        if(static::getCreateLoginUserField()){
            unset($data[static::getCreateLoginUserField()]);
        }
        if(static::getUpdateLoginUserField()){
            unset($data[static::getUpdateLoginUserField()]);
        }
        if(static::getDeleteLoginUserField()){
            unset($data[static::getDeleteLoginUserField()]);
        }
        

        //onEditBefore请用doSaveDataAfter
        $info=clone $beforeInfo;
        $info->save($data);

        FieldDo::doSaveAfter($saveFields,$data,$beforeInfo,$info,$parentInfo);
        $this->onEditAfter($info,$data,$parentInfo,$beforeInfo);

        if($haveDoStep&&$saveStepInfo){
            $saveStepInfo->doSaveAfter($beforeInfo,$info,$parentInfo,$fields,$data);
            if(static::hasNextStepField()){
                $nestStep=$this->fields()->getNextStepInfo($info,$parentInfo);
                $info[static::getNextStepField()]=$nestStep===null?'':$nestStep->getStep();
            }
        }
        $info->save();

        $returnSaveData=$data;
        return $info;
    }


    /**
     * 保存前 验证数据，数据处理
     * @param array $postData
     * @param FieldCollection|null $fields
     * @param bool $isExcelDo 是否excel操作
     * @param BaseModel|null $parentInfo base表数据
     * @param BaseModel|null $beforeInfo 数据之前的老值
     * @param FieldCollection|null $saveFields 当前操作要保存到数据库的字段
     * @return array
     * @throws \think\Exception
     */
    final protected function doSaveData(array $postData,BaseModel $beforeInfo,FieldCollection $fields=null,bool $isExcelDo=false,BaseModel $parentInfo=null,FieldCollection &$saveFields=null):array{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        is_null($fields)&&$fields=$this->fields();//新的字段集合对象，批量添加时都用一个会保存的值共用问题

        $id=empty($postData['id'])?0:$postData['id'];


        $this->doSaveDataBefore($fields,$postData,$isExcelDo,$id,$parentInfo,$beforeInfo);
        $saveData=$fields->setSave($postData,$beforeInfo,$isExcelDo,$saveFields)->getSave();
        $this->doSaveDataAfter($saveData,$id,$parentInfo,$beforeInfo);

        return $saveData;
    }
}