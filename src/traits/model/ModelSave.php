<?php


namespace tpScriptVueCurd\traits\model;


use app\admin\model\SystemAdmin;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;

/**
 * Trait ModelSave
 * @package tpScriptVueCurd\traits\model
 * @author tj 1079798840@qq.com
 */
trait ModelSave
{


    /**
     * 新增修改数据
     * @param array $postData           要保存的数据
     * @param FieldCollection|null $fields
     * @param BaseModel|null $baseInfo
     * @param VueCurlModel|null $beforeInfo
     * @return $this
     * @throws \think\Exception
     */
    public function saveInfo(array $postData,FieldCollection $fields=null,BaseModel $baseInfo=null,VueCurlModel $beforeInfo=null): self
    {
        $data=$this->doSaveData($postData,$fields,false,$baseInfo,$beforeInfo);


        if(empty($postData['id'])){
            throw new \think\Exception('缺少ID');
        }

        $data['id']=$postData['id'];
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
            unset($data[static::getRegionPidField()]);
        }
        if(static::getRegionField()){
            unset($data[static::getRegionField()]);
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