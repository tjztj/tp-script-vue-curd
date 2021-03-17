<?php


namespace tpScriptVueCurd\base\model;


use app\admin\model\SystemAdmin;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;

/**
 * Class BaseChildModel
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\model
 */
abstract class BaseChildModel extends VueCurlModel
{

    public function base()
    {
        return $this->belongsTo(static::parentModelClassPath(), static::parentField());
    }

    /**
     * 关联字段（默认为base_id）,子类重写此方法改变
     * @return string
     */
    public static function parentField():string{
        return 'base_id';
    }


    /**
     * 返回父表的class
     * @return string|BaseModel
     */
    public static function parentModelClassPath():string{
        static $return;
        if(!$return){
            $return=static::getControllerClass()::parentControllerClassPath()::modelClassPath();
        }
        return $return;
    }

    /**
     * 添加子数据
     * @param array $postData        添加的数据
     * @param BaseModel $baseInfo   所属的数据
     * @param bool $isExcelDo       是否excel操作
     * @return $this
     * @throws \think\Exception
     */
    public function addInfo(array $postData,BaseModel $baseInfo,bool $isExcelDo=false):self{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        $fields=$this->fields()->filter(fn(ModelField $v)=>$v->name()!==static::getRegionField()&&$v->name()!==static::getRegionPidField());
        $data=$this->doSaveData($postData,$fields,$isExcelDo,$baseInfo);
        $data[static::parentField()]=$baseInfo->id;
        static::getRegionField()===''||$this->fields()->filter(fn($v)=>$v->name()===static::getRegionField())->isEmpty()||$data[static::getRegionField()]=$baseInfo[static::getRegionField()];
        static::getRegionPidField()===''||$this->fields()->filter(fn($v)=>$v->name()===static::getRegionPidField())->isEmpty()||$data[static::getRegionPidField()]=$baseInfo[static::getRegionPidField()];

        if(static::getCreateLoginUserField()){
            $data[static::getCreateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        if(static::getUpdateLoginUserField()){
            $data[static::getUpdateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        //onAddBefore请用doSaveDataAfter
        $info=self::create($data);
        $this->onAddAfter($info,$data,$baseInfo);
        return $info;
    }

    final public function del(array $ids): \think\Collection
    {
        return $this->doDel($ids);
    }


    protected function doSaveDataBefore(FieldCollection $fields,array &$postData,bool $isExcelDo,int $id,BaseModel $baseInfo,?VueCurlModel $beforeInfo):void{} //执行doSaveData前（钩子）
    protected function doSaveDataAfter(array $saveData,int $id,BaseModel $baseInfo,?VueCurlModel $beforeInfo):array{return $saveData;} //执行doSaveData后（钩子）
    protected function onAddAfter(VueCurlModel $info,array $postData,BaseModel $baseInfo): void{}//添加后钩子
    protected function onEditAfter(VueCurlModel $info,array $postData,BaseModel $baseInfo,?VueCurlModel $beforeInfo): void{}//修改后钩子
}