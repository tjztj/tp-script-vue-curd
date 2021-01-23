<?php


namespace tpScriptVueCurd\base\model;


use app\admin\model\SystemAdmin;
use tpScriptVueCurd\base\controller\BaseChildController;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\FieldCollection;

/**
 * Class BaseModel
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\model
 */
abstract class BaseModel extends VueCurlModel
{

    /**
     * 添加基本事项
     * @param array $oldData
     * @param FieldCollection|null $fields
     * @param bool $isExcelDo 是否excel操作
     * @return $this
     * @throws \think\Exception
     */
    public function addInfo(array $oldData,FieldCollection $fields=null,bool $isExcelDo=false):self{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        //验证表是否能执行此操作
        if(!is_subclass_of($this,BaseModel::class)) {
            throw new \think\Exception('当前model不能执行此操作');
        }
        $data=$this->doSaveData($oldData,$fields,$isExcelDo);
        //TODO::地区权限验证
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
        /* @var Controller $v */
        $controll=static::getControllerClass();

        if($controll::type()==='base_have_child'){
            foreach ($controll::childControllerClassPathList() as $v){
                /* @var BaseChildController $v */
                $haveChildDataBaseId=$v::modelClassPath()::where('base_id','in',$ids)->max('base_id');
                if($haveChildDataBaseId){
                    throw new \think\Exception('需先删除下面的'.$v::modelClassPath()::getTitle().'数据');
                }
            }
        }
        $this->doDel($ids);
    }
}