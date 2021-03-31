<?php


namespace tpScriptVueCurd\traits\model;


use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;

/**
 * Trait ModelSave
 * @package tpScriptVueCurd\traits\model
 * @author tj 1079798840@qq.com
 */
trait ModelSave
{


    /**
     * 修改数据
     * @param array $postData           要保存的数据
     * @param FieldCollection $fields
     * @param BaseModel|null $baseInfo
     * @param VueCurlModel $beforeInfo
     * @return $this
     * @throws \think\Exception
     */
    public function saveInfo(array $postData,FieldCollection $fields,BaseModel $baseInfo=null,VueCurlModel $beforeInfo): self
    {
        //为了防止在doSaveData中被删除，在这里先获取了
        $saveStepInfo=$fields->saveStepInfo??null;

        $data=$this->doSaveData($postData,$fields,false,$baseInfo,$beforeInfo);

        if(empty($postData['id'])){
            throw new \think\Exception('缺少ID');
        }

        $data['id']=$postData['id'];

        //没有设置当前步骤
        if(!isset($data[static::getStepField()])&&$fields->stepIsEnable()){
            if(!$saveStepInfo){
                throw new \think\Exception('未能获取到当前步骤信息');
            }
            $stepData=[
                'step'=>$saveStepInfo->getStep(),
                'time'=>time(),
                'user'=>staticTpScriptVueCurdGetLoginData()['id'],
                'back'=>'0',//后退步数
            ];
            if($beforeInfo[static::getStepField()]){
                $stepsArr=is_string($beforeInfo[static::getStepField()])?json_decode($beforeInfo[static::getStepField()],true):$beforeInfo[static::getStepField()];
                $nowStep=end($stepsArr);
                if($nowStep['step']!==$saveStepInfo->getStep()){
                    //新的步骤
                    $stepsArr[]=$stepData;
                }
            }else{
                $stepsArr=[$stepData];
            }
            $data[static::getStepField()]=json_encode($stepsArr);
        }

        unset( $data['create_time']
            , $data['update_time']
            , $data['delete_time']
        );
        if(static::getCreateLoginUserField()){
            unset($data[static::getCreateLoginUserField()]);
        }
        if(static::getUpdateLoginUserField()){
            unset($data[static::getUpdateLoginUserField()]);
        }
        if(static::getDeleteLoginUserField()){
            unset($data[static::getDeleteLoginUserField()]);
        }
        if(static::getRegionPidField()){
            $fs=$fields->filter(fn(ModelField $v)=>$v->name()===static::getRegionPidField());
            if($fs->count()===0||$fs->findByName(static::getRegionPidField())->canEdit()===false){
                unset($data[static::getRegionPidField()]);
            }
        }
        if(static::getRegionField()){
            $fs=$fields->filter(fn(ModelField $v)=>$v->name()===static::getRegionField());
            if($fs->count()===0||$fs->findByName(static::getRegionField())->canEdit()===false){
                unset($data[static::getRegionField()]);
            }
        }

        //onEditBefore请用doSaveDataAfter
        $info=self::update($data);
        $this->onEditAfter($info,$data,$baseInfo,$beforeInfo);
        return $info;
    }


    /**
     * 保存前 验证数据，数据处理
     * @param array $postData
     * @param FieldCollection|null $fields
     * @param bool $isExcelDo                   是否excel操作
     * @param BaseModel|null $baseInfo          base表数据
     * @param VueCurlModel|null $beforeInfo     数据之前的老值
     * @return array
     * @throws \think\Exception
     */
    final protected function doSaveData(array $postData,FieldCollection $fields=null,bool $isExcelDo=false,BaseModel $baseInfo=null,VueCurlModel $beforeInfo=null):array{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        is_null($fields)&&$fields=$this->fields();//新的字段集合对象，批量添加时都用一个会保存的值共用问题

        $id=empty($postData['id'])?0:$postData['id'];


        //切面
        if($this instanceof BaseChildModel){
            $this->doSaveDataBefore($fields,$postData,$isExcelDo,$id,$baseInfo,$beforeInfo);
            $saveData=$fields->setSave($postData,$isExcelDo)->getSave();
            $saveData=$this->doSaveDataAfter($saveData,$id,$baseInfo,$beforeInfo);
        }else if($this instanceof BaseModel){
            $this->doSaveDataBefore($fields,$postData,$isExcelDo,$id,$beforeInfo);
            $saveData=$fields->setSave($postData,$isExcelDo)->getSave();
            $saveData=$this->doSaveDataAfter($saveData,$id,$beforeInfo);
        }else{
            $saveData=$fields->setSave($postData,$isExcelDo)->getSave();
        }
        return $saveData;
    }
}