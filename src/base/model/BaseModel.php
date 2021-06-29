<?php


namespace tpScriptVueCurd\base\model;


use tpScriptVueCurd\base\controller\BaseChildController;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;
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
     * @param array $postData
     * @param FieldCollection $fields
     * @param bool $isExcelDo 是否excel操作
     * @param array $returnSaveData
     * @return $this
     * @throws \think\Exception
     */
    public function addInfo(array $postData,FieldCollection $fields,bool $isExcelDo=false,array &$returnSaveData=[]):self{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        //验证表是否能执行此操作
        if(!is_subclass_of($this, __CLASS__)) {
            throw new \think\Exception('当前model不能执行此操作');
        }

        if(!$this->checkRowAuth($fields,null,'add')){
            throw new \think\Exception('不能添加此栏目信息');
        }
        FieldDo::doSaveBefore($fields,$postData,null,null);

        //为了防止在doSaveData中被删除，在这里先获取了
        $saveStepInfo=$fields->saveStepInfo??null;

        $data=$this->doSaveData($postData,$fields,$isExcelDo);

        FieldDo::doSaveBeforeChecked($fields,$data,null,null);

        //没有设置当前步骤， excel导入不分步骤
        $haveDoStep=!isset($data[static::getStepField()])&&!$isExcelDo&&$fields->stepIsEnable();
        if($haveDoStep){
            if(!$saveStepInfo){
                throw new \think\Exception('未能获取到当前步骤信息');
            }
            $saveStepInfo->doSaveBefore($data,null,null,$fields);
            //如果已经在doSaveBefore中设置了，就不再设置
            isset($data[static::getStepField()])||$data[static::getStepField()]=$saveStepInfo->getNewStepJson(null);
        }
        if(isset($data[static::getStepField()])){
            //为了防止赋值错误，修正为正确的步骤的值，主要是back
            $data[static::getStepField()]=FieldStep::correctSteps($data[static::getStepField()]);
        }


        if(static::getCreateLoginUserField()){
            $data[static::getCreateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        if(static::getUpdateLoginUserField()){
            $data[static::getUpdateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        //onAddBefore请用doSaveDataAfter
        $info=static::create($data);

        FieldDo::doSaveAfter($fields,$data,null,$info,null);
        $this->onAddAfter($info,$data);
        if($haveDoStep&&$saveStepInfo){
            $saveStepInfo->doSaveAfter(null,$info,null,$fields,$data);
            $nestStep=$this->fields()->getNextStepInfo($info);
            $info[static::getNestStepField()]=$nestStep===null?'':$nestStep->getStep();
            $info->save();
        }

        $returnSaveData=$data;
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

    final protected function delCheckRowAuth(\think\Collection $list,array $ids): void
    {
        $fields=$this->fields();
        $list->each(function(self $v)use($fields,$ids){
            if($v->checkRowAuth($fields,null,'del')===false){
                throw new \think\Exception('您不能删除第'.(array_search($v->id,$ids)+1).'条数据');
            }
        });
    }



    protected function doSaveDataBefore(FieldCollection $fields,array &$postData,bool $isExcelDo,int $id,?VueCurlModel $beforeInfo):void{} //执行doSaveData前（钩子）
    protected function doSaveDataAfter(array &$saveData,int $id,?VueCurlModel $beforeInfo):void{} //执行doSaveData后（钩子）
    protected function onAddAfter(VueCurlModel $info,array $postData): void{}//添加后钩子
    protected function onEditAfter(VueCurlModel $info,array $postData,?VueCurlModel $beforeInfo): void{}//修改后钩子
}