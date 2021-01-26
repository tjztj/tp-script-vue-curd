<?php


namespace tpScriptVueCurd\base\model;


use app\admin\model\SystemAdmin;
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
        return $this->belongsTo(static::parentModelClassPath(), 'pub_id');
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
     * @param array $oldData        添加的数据
     * @param BaseModel $baseInfo   所属的数据
     * @param bool $isExcelDo       是否excel操作
     * @return $this
     * @throws \think\Exception
     */
    public function addInfo(array $oldData,BaseModel $baseInfo,bool $isExcelDo=false):self{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        $fields=$this->fields()->filter(fn(ModelField $v)=>$v->name()!=='system_region_id'&&$v->name()!=='system_region_pid');
        $data=$this->doSaveData($oldData,$fields,$isExcelDo);
        $data['base_id']=$baseInfo->id;
        $this->fields()->filter(fn($v)=>$v->name()==='system_region_id')->isEmpty()||$data['system_region_id']=$baseInfo->system_region_id;
        $this->fields()->filter(fn($v)=>$v->name()==='system_region_pid')->isEmpty()||$data['system_region_pid']=$baseInfo->system_region_pid;
        //TODO::需要加入方法getLoginData
        $data['create_system_admin_id']=getLoginData()['id'];
        $data['update_system_admin_id']=getLoginData()['id'];
        //onAddBefore请用doSaveDataAfter
        $info=self::create($data);
        $this->onAddAfter($info,$data);
        return $info;
    }

    public function del(array $ids): void
    {
        $this->doDel($ids);
    }
}