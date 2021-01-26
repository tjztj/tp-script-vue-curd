<?php


namespace tpScriptVueCurd\traits\model;


use app\admin\model\SystemAdmin;
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
     * @param array $oldData    要保存的数据
     * @param FieldCollection|null $fields
     * @throws \think\Exception
     */
    public function saveInfo(array $oldData,FieldCollection $fields=null): self
    {
        $data=$this->doSaveData($oldData,$fields);


        if(empty($oldData['id'])){
            throw new \think\Exception('缺少ID');
        }

        $data['id']=$oldData['id'];
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
        $this->onEditAfter($info,$data);
        return $info;
    }



    /**
     * 保存前 验证数据，数据处理
     * @param array $oldData
     * @param FieldCollection|null $fields
     * @param bool $isExcelDo  是否excel操作
     * @return array
     * @throws \think\Exception
     */
    final protected function doSaveData(array $oldData,FieldCollection $fields=null,bool $isExcelDo=false):array{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        is_null($fields)&&$fields=$this->fields();//新的字段集合对象，批量添加时都用一个会保存的值共用问题

        $id=empty($oldData['id'])?0:$oldData['id'];


        //切面
        $this->doSaveDataBefore($fields,$oldData,$isExcelDo,$id);
        $saveData=$fields->setSave($oldData,$isExcelDo)->getSave();
        $saveData=$this->doSaveDataAfter($saveData,$id);

        return $saveData;
    }


    protected function doSaveDataBefore(FieldCollection $fields,array &$oldData,bool $isExcelDo,int $id):void{} //执行doSaveData前（钩子）
    protected function doSaveDataAfter(array $saveData,int $id):array{return $saveData;} //执行doSaveData后（钩子）
    protected function onAddAfter(VueCurlModel $info,array $oldData): void{}//添加后钩子
    protected function onEditAfter(VueCurlModel $info,array $oldData): void{}//修改后钩子
}