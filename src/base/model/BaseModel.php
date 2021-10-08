<?php


namespace tpScriptVueCurd\base\model;



use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
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

        if($parentInfo){
            $fields=$fields->filter(fn(ModelField $v)=>$v->name()!==static::getRegionField()&&$v->name()!==static::getRegionPidField());
        }

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
            static::getRegionField()===''||$this->fields()->filter(fn($v)=>$v->name()===static::getRegionField())->isEmpty()||$data[static::getRegionField()]=$parentInfo[static::getRegionField()];
            static::getRegionPidField()===''||$this->fields()->filter(fn($v)=>$v->name()===static::getRegionPidField())->isEmpty()||$data[static::getRegionPidField()]=$parentInfo[static::getRegionPidField()];
        }

        if(static::getCreateLoginUserField()){
            $data[static::getCreateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        if(static::getUpdateLoginUserField()){
            $data[static::getUpdateLoginUserField()]=staticTpScriptVueCurdGetLoginData()['id'];
        }
        //onAddBefore请用doSaveDataAfter
        $info=static::create($data);

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

    final public function del(array $ids): \think\Collection
    {
        if($this->controller->childControllers){
            foreach ($this->controller->childControllers as $v){
                /* @var Controller $v */
                $haveChildDataBaseId=(clone $v->model)->where($v->model::parentField(),'in',$ids)->max($v->model::parentField());
                if($haveChildDataBaseId){
                    throw new \think\Exception('需先删除下面的'.$v->title.'数据',ErrorCode::DELETE_HAVE_CHILD);
                }
            }
        }


        return $this->doDel($ids);
    }

    final protected function delCheckRowAuth(\think\Collection $list,array $ids): void
    {
        $parents=[];
       if( $this->controller->parentController){
           (clone $this->controller->parentController->model)->where('id','in',$list->column(static::parentField()))->select()->each(function(BaseModel $v)use(&$parents){
               $parents[$v->id]=$v;
           });
       }

        $fields=$this->fields();
        $list->each(function(self $v)use($fields,$ids,$parents){
            if($v->checkRowAuth($fields,$parents[$v[static::parentField()]]??null,'del')===false){
                throw new \think\Exception('您不能删除第'.(array_search($v->id,$ids)+1).'条数据');
            }
        });
    }



    protected function doSaveDataBefore(FieldCollection $fields,array &$postData,bool $isExcelDo,int $id,?BaseModel $parentInfo,BaseModel $beforeInfo):void{} //执行doSaveData前（钩子）
    protected function doSaveDataAfter(array &$saveData,int $id,?BaseModel $parentInfo,BaseModel $beforeInfo):void{} //执行doSaveData后（钩子）
    protected function onAddAfter(BaseModel $info,array $postData,?BaseModel $parentInfo): void{}//添加后钩子
    protected function onEditAfter(BaseModel $info,array $postData,?BaseModel $parentInfo,BaseModel $beforeInfo): void{}//修改后钩子
}