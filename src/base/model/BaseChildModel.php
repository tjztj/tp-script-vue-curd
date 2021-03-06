<?php


namespace tpScriptVueCurd\base\model;


use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;

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
     * @param array $postData           添加的数据
     * @param BaseModel $baseInfo       所属的数据
     * @param FieldCollection $fields   添加的字段信息
     * @param bool $isExcelDo           是否excel操作
     * @return $this
     * @throws \think\Exception
     */
    public function addInfo(array $postData,BaseModel $baseInfo,FieldCollection $fields,bool $isExcelDo=false,array &$returnSaveData=[]):self{

        #########################################################################################
        ######  此方法不能有数据库查询操作，要获取其他数据，一律传参。因为我批量添加的时候也是执行此方法  ######
        #########################################################################################

        FieldDo::doSaveBefore($fields,$postData,null,$baseInfo);
        $fields=$fields->filter(fn(ModelField $v)=>$v->name()!==static::getRegionField()&&$v->name()!==static::getRegionPidField());


        if(!$this->checkRowAuth($fields,$baseInfo,'add')){
            throw new \think\Exception('不能添加此栏目信息');
        }

        //为了防止在doSaveData中被删除，在这里先获取了
        $saveStepInfo=$fields->saveStepInfo??null;

        $data=$this->doSaveData($postData,$fields,$isExcelDo,$baseInfo,null,$saveFields);

        FieldDo::doSaveBeforeChecked($saveFields,$data,null,$baseInfo);


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
                $saveStepInfo->doSaveBefore($data,null,$baseInfo,$fields);
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
        $info=static::create($data);

        FieldDo::doSaveAfter($saveFields,$data,null,$info,$baseInfo);
        $this->onAddAfter($info,$data,$baseInfo);
        if($haveDoStep&&$saveStepInfo){
            $saveStepInfo->doSaveAfter(null,$info,$baseInfo,$fields,$data);
            if(static::hasNextStepField()){
                $nestStep=$this->fields()->getNextStepInfo($info,$baseInfo);
                $info[static::getNextStepField()]=$nestStep===null?'':$nestStep->getStep();
            }
        }
        $info->save();

        $returnSaveData=$data;
        return $info;
    }

    final public function del(array $ids): \think\Collection
    {
        return $this->doDel($ids);
    }

    final protected function delCheckRowAuth(\think\Collection $list,array $ids): void
    {
        $parents=[];
        static::parentModelClassPath()::where('id','in',$list->column(static::parentField()))->select()->each(function(BaseModel $v)use(&$parents){
            $parents[$v->id]=$v;
        });
        $fields=$this->fields();
        $list->each(function(self $v)use($fields,$parents,$ids){
            if($v->checkRowAuth($fields,$parents[$v[static::parentField()]],'del')===false){
                throw new \think\Exception('您不能删除第'.(array_search($v->id,$ids)+1).'条数据');
            }
        });
    }


    protected function doSaveDataBefore(FieldCollection $fields,array &$postData,bool $isExcelDo,int $id,BaseModel $baseInfo,?VueCurlModel $beforeInfo):void{} //执行doSaveData前（钩子）
    protected function doSaveDataAfter(array &$saveData,int $id,BaseModel $baseInfo,?VueCurlModel $beforeInfo):void{} //执行doSaveData后（钩子）
    protected function onAddAfter(VueCurlModel $info,array $postData,BaseModel $baseInfo): void{}//添加后钩子
    protected function onEditAfter(VueCurlModel $info,array $postData,BaseModel $baseInfo,?VueCurlModel $beforeInfo): void{}//修改后钩子
}