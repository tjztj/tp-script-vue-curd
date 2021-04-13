<?php


namespace tpScriptVueCurd\base\model;


use tpScriptVueCurd\base\controller\BaseChildController;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\tool\ErrorCode;

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
     * @param FieldCollection $fields
     * @param bool $isExcelDo 是否excel操作
     * @return $this
     * @throws \think\Exception
     */
    public function addInfo(array $postData,FieldCollection $fields,bool $isExcelDo=false):self{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        //验证表是否能执行此操作
        if(!is_subclass_of($this,BaseModel::class)) {
            throw new \think\Exception('当前model不能执行此操作');
        }

        //为了防止在doSaveData中被删除，在这里先获取了
        $saveStepInfo=$fields->saveStepInfo??null;

        $data=$this->doSaveData($postData,$fields,$isExcelDo);

        //没有设置当前步骤， excel导入不分步骤
        if(!isset($data[static::getStepField()])&&!$isExcelDo&&$fields->stepIsEnable()){
            if(!$saveStepInfo){
                throw new \think\Exception('未能获取到当前步骤信息');
            }
            $saveStepInfo->doSaveBefore($data,null,null,$fields);
            //如果已经在doSaveBefore中设置了，就不再设置
            isset($data[static::getStepField()])||$data[static::getStepField()]=$saveStepInfo->getNewStepJson(null);
        }

        //TODO::地区权限验证
        if(static::getCreateLoginUserField()){
            $data[static::getCreateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        if(static::getUpdateLoginUserField()){
            $data[static::getUpdateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        //onAddBefore请用doSaveDataAfter
        $info=self::create($data);
        $this->onAddAfter($info,$data);

        return $info;
    }

    final public function del(array $ids): \think\Collection
    {
        /* @var Controller $v */
        $controll=static::getControllerClass();

        if($controll::type()==='base_have_child'){
            foreach ($controll::childControllerClassPathList() as $v){
                /* @var BaseChildController $v */
                $haveChildDataBaseId=$v::modelClassPath()::where($v::modelClassPath()::parentField(),'in',$ids)->max($v::modelClassPath()::parentField());
                if($haveChildDataBaseId){
                    throw new \think\Exception('需先删除下面的'.$v::getTitle().'数据',ErrorCode::DELETE_HAVE_CHILD);
                }
            }
        }
        return $this->doDel($ids);
    }



    protected function doSaveDataBefore(FieldCollection $fields,array &$postData,bool $isExcelDo,int $id,?VueCurlModel $beforeInfo):void{} //执行doSaveData前（钩子）
    protected function doSaveDataAfter(array $saveData,int $id,?VueCurlModel $beforeInfo):array{return $saveData;} //执行doSaveData后（钩子）
    protected function onAddAfter(VueCurlModel $info,array $postData): void{}//添加后钩子
    protected function onEditAfter(VueCurlModel $info,array $postData,?VueCurlModel $beforeInfo): void{}//修改后钩子
}