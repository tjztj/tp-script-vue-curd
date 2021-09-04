<?php


namespace tpScriptVueCurd\traits\controller;


use think\Collection;
use think\db\Query;
use think\Request;
use tpScriptVueCurd\base\controller\BaseChildController;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\field\FilesField;
use tpScriptVueCurd\field\SelectField;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;
use tpScriptVueCurd\option\FieldStepCollection;
use tpScriptVueCurd\option\FunControllerIndexData;
use tpScriptVueCurd\option\FunControllerIndexPage;
use tpScriptVueCurd\option\FunControllerListChildBtn;

/**
 * 为了方便有时候子控制器也使用
 * Trait BaseIndex
 * @property Request $request
 * @property FunControllerIndexPage $indexPageOption
 * @property FieldCollection $fields
 * @property BaseChildModel|BaseModel $model
 * @property BaseModel $baseModel
 * @package tpScriptVueCurd\traits\controller
 */
trait BaseIndex
{


    /**
     * #title 数据列表
     * @return mixed|\think\response\Json|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function index(){
        //是否有父表
        $baseInfo=null;
        try{
            $this->indexBeforeSetBase($baseInfo);
        }catch (\Exception $e){
            $this->error($e);
        }



        if($this->request->isAjax()){
            $model=$this->indexListModelWhere($this->model,$baseInfo);
            $model=$model->order($this->getListOrder());
            $option=$this->indexListSelect($model);
            $list=$option->sourceList;

            $this->setListDataChildBaseInfo($baseInfo,$list);

            $list=$this->setListDataStep($list,$baseInfo);
            $list=$this->setListDataRowChildBtn($list);
            $list=$this->setListDataRowAuth($list,$baseInfo);



            //字段钩子
            try{
                if(is_array($baseInfo)){
                    foreach ($list as $v){
                        FieldDo::doIndex($this->fields,\think\model\Collection::make([$v]),$baseInfo[$v[$this->model::parentField()]]??null);
                    }
                }else{
                    FieldDo::doIndex($this->fields,$list,$baseInfo);
                }
            }catch(\Exception $e){
                $this->error($e);
            }


            //字段显示处理
            $option->data=$list->toArray();
            foreach ($option->data as $k=>$v){
                $this->fields->doShowData($option->data[$k]);
            }
            $option->baseInfo=is_array($baseInfo)?null:$baseInfo;


            //控制器数据处理钩子
            try{
                $this->indexData($option);
            }catch(\Exception $e){
                $this->error($e);
            }

            $this->success($option->toArray());
        }



        try{
            //要改fields，可以直接在 indexShowBefore 里面$this->fields
            $this->indexShowBefore($baseInfo);
            //字段钩子触发
            FieldDo::doIndexShow($this->fields,$baseInfo,$this);
        }catch (\Exception $e){
            $this->error($e);
        }



        //是否显示添加按钮
        try{
            $rowAuthAdd=$this->model->checkRowAuth($this->getRowAuthAddFields(),$baseInfo,'add');
        }catch (\Exception $e){
            $rowAuthAdd=false;
        }


        $filterFields=clone $this->fields;
        $paranFilterData=$this->request->param('filter_data','',null);
        $filterData=$paranFilterData?json_decode($paranFilterData,true):null;
        $showFilter=$this->request->param('show_filter/d',1)===1;
        $this->indexFilterBefore($filterFields,$filterData,$showFilter);
        $this->setIndexFilterAddStep($filterFields,$filterData);




        $listColumns=array_values($this->fields->listShowItems()->toArray());
        $showTableTool=$this->request->param('show_table_tool/d',1)===1;
        $data=[
            'model'=>static::modelClassPath(),
            'modelName'=>class_basename(static::modelClassPath()),
            'indexPageOption'=>$this->indexPageOption,
            'listColumns'=>$listColumns,
            'groupGroupColumns'=>$this->fields->groupItems? FieldCollection::groupListByItems($listColumns):null,//不管显示是不是一个组，只要groupItems有，列表就分组
            'listUrl'=>$this->request->url(),
            'editUrl'=>url('edit')->build(),
            'showUrl'=>url('show')->build(),
            'delUrl'=>url('del')->build(),
            'downExcelTplUrl'=>url('downExcelTpl',['base_id'=>$baseInfo?$baseInfo->id:0])->build(),
            'importExcelTplUrl'=>url('importExcelTpl',['base_id'=>$baseInfo?$baseInfo->id:0])->build(),
            'title'=>static::getTitle(),
            'childs'=>[],//会在BaseHaveChildController中更改
            'filterConfig'=>$filterFields->getFilterShowData(),
            'filter_data'=>$filterData?:null,
            'showFilter'=>$showFilter,
            'showTableTool'=>$showTableTool,
            'canEdit'=>$showTableTool,
            'canDel'=>$showTableTool,
            'auth'=>[
                'add'=>true,
                'edit'=>true,
                'del'=>true,
                'importExcelTpl'=>true,
                'downExcelTpl'=>true,
                'stepAdd'=>$this->getAuthAdd($baseInfo),
                'rowAuthAdd'=>$rowAuthAdd
            ],
            'baseInfo'=>$baseInfo,
            'fieldComponents'=>$this->fields->listShowItems()->getComponents('index'),
            'filterComponents'=>$filterFields->getFilterComponents(),
            'fieldStepConfig'=>$this->fields->getStepConfig(),
        ];

        if($this->type()==='base_have_child'){
            $this->indexFetchDoChild($data);
        }

        try{
            $this->indexFetch($data);
        }catch (\Exception $e){
            $this->error($e);
        }

        return $this->showTpl('index',$data);
    }



    /**
     * BaseIndex 的列表中对$baseInfo赋值的逻辑
     * @param BaseModel|null $baseInfo
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function indexBeforeSetBase(?BaseModel &$baseInfo): void
    {
        if($baseInfo){
            //如果有了，就不设置了
            return;
        }
        if(empty($this->baseModel)){
            //如果没有父表
            return;
        }
        $baseId=$this->request->param('base_id/d',0);
        if(empty($baseId)){
            return;
        }
        $baseInfo=$this->baseModel->find($baseId);
        if(!$baseInfo){
            throw new \think\Exception('未能获取到相关父表信息');
        }
    }

    /**
     * 列表显示的where条件处理
     * @param BaseModel|BaseChildModel $model
     * @param BaseModel|null $baseInfo
     * @return Query|BaseChildModel|BaseModel
     */
    protected function indexListModelWhere($model,?BaseModel $baseInfo)
    {
        $filterFields=clone $this->fields;
        $filterData=$filterFields->getParamFilterData();
        $showFilter=true;
        $this->indexFilterBefore($filterFields,$filterData,$showFilter);
        $this->setIndexFilterAddStep($filterFields,$filterData);

        return $model
            ->where(function (Query $query)use($baseInfo){
                $id=$this->request->param('id/d');
                empty($id)||$query->where('id',$id);
                $baseInfo === null || $query->where($this->model::parentField(),$baseInfo->id);
            })
            ->where(function(Query $query){
                //这里不应该抛出异常
                $this->indexListWhere($query);
            })
            ->where($filterFields->getFilterWhere($filterData))
            ->where(function(Query $query){
                $childFilterData=$this->request->param('childFilterData',null,null);
                if($childFilterData){
                    $childFilterData=json_decode($childFilterData,true);
                }
                if(empty($childFilterData)){
                    return [];
                }
                if(static::type()==='base_have_child'){
                    foreach (static::childModelObjs() as $childModel){
                        /**
                         * @var BaseChildModel $childModel
                         */
                        $type=class_basename($childModel);
                        if(!empty($childFilterData[$type])){
                            $sql=$childModel->where($childModel->fields()->getFilterWhere($childFilterData[$type]))->field($childModel::parentField())->buildSql();
                            if($sql!==$childModel->field($childModel::parentField())->buildSql()){
                                $query->whereRaw('id IN '.$sql);
                            }
                        }
                    }
                }
            })
            ->where($this->stepAuthWhere($filterFields));
    }



    /**
     * 列表所有数据的父表设置
     * @param null|array|BaseModel $oldBaseInfo
     * @param Collection|\think\model\Collection $childList
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function setListDataChildBaseInfo(&$oldBaseInfo,$childList):void{
        if(!is_null($oldBaseInfo)){
            //如果已经有了，不需要再设值
            return;
        }
        if(empty($this->baseModel)){
            //如果当前没有父表
            return;
        }
        if($childList->isEmpty()){
            //没有子数据，不需要设置父表
            return;
        }

        $baseInfo=[];
        foreach ($childList as $v){
            $baseInfo[$v[$this->model::parentField()]]=null;
        }
        foreach ($this->baseModel->where('id','in',array_keys($baseInfo))->select() as $val){
            $baseInfo[$val->id]=$val;
        }
        $oldBaseInfo=$baseInfo;
    }

    /**
     * 设置列表数据步骤信息
     * @param Collection|\think\model\Collection $list
     * @param null|array|BaseModel $baseInfo
     * @return Collection|\think\model\Collection
     */
    protected function setListDataStep($list,$baseInfo){
        return $list->map(function(VueCurlModel $info)use($baseInfo){
            if(!$this->fields->stepIsEnable()){
                return $info;
            }
            if(is_array($baseInfo)){
                $baseInfo=$baseInfo[$info[$this->model::parentField()]]??null;
            }


            $stepInfo=$this->fields->getCurrentStepInfo($info,$baseInfo);
            $stepInfo === null ||$stepInfo=clone $stepInfo;
            $info->stepInfo=$stepInfo?$stepInfo->listRowDo($info,$baseInfo,$this->fields)->toArray():null;

            $nextStepInfo=$this->fields->getNextStepInfo($info,$baseInfo);
            $nextStepInfo === null || $nextStepInfo=clone $nextStepInfo;
            $info->nextStepInfo=$nextStepInfo?$nextStepInfo->toArray():null;
            $info->stepNextCanEdit= $info->nextStepInfo && $nextStepInfo->authCheck($info, $baseInfo, $this->fields->getFilterStepFields($nextStepInfo, true, $info,$baseInfo));


            $stepFields=$stepInfo?$this->fields->getFilterStepFields($stepInfo,false,$info,$baseInfo):FieldCollection::make();
            $info->stepFields=$stepFields->column('name');
            $info->stepCanEdit= $stepInfo && $stepInfo->authCheck($info, $baseInfo, $stepFields);


            return $info;
        });
    }

    /**
     * 设置数据 子表各按钮配置
     * @param Collection|\think\model\Collection $list
     * @return Collection|\think\model\Collection
     */
    protected function setListDataRowChildBtn($list){
        return $list->map(function(VueCurlModel $info){
            if($this->type()!=='base_have_child'){
                return $info;
            }
            $childBtns=[];
            foreach (static::childControllerClassPathList() as $childControllerClass){
                /* @var $childControllerClass BaseChildController|string */
                $btn=new FunControllerListChildBtn();
                $childControllerClass::baseListBtnText($btn,$info);
                if(!isset($btn->url)||is_null($btn->url)){
                    $btn->url=url(str_replace(['\\','._'],['.','.'],parse_name(ltrim(str_replace($this->app->getNamespace().'\\controller\\','',$childControllerClass),'\\'))).'/index',['base_id'=>$info->id])->build();
                }
                $childBtns[class_basename($childControllerClass::modelClassPath())]=$btn;
            }
            $info->childBtns=$childBtns;
            return $info;
        });
    }

    /**
     * 设置数据 __auth 的值，是否具有增删改的权限
     * @param Collection|\think\model\Collection $list
     * @param null|array|BaseModel $baseInfo
     * @return Collection|\think\model\Collection
     */
    protected function setListDataRowAuth($list,$baseInfo){
        return $list->map(fn(VueCurlModel $info)=>$info->rowSetAuth($this->fields,is_array($baseInfo)?($baseInfo[$info[$this->model::parentField()]]??null):$baseInfo,['show','edit','del']));
    }

    /**
     * 如果启用了步骤，在列表筛选中会出现当前步骤筛选
     * @param FieldCollection $filterFields
     * @param array|null $filterData
     * @throws \think\Exception
     */
    protected function setIndexFilterAddStep(FieldCollection $filterFields,?array $filterData){
        if($filterFields->stepIsEnable()){
            $steps=[];
            $filterFields->each(function (ModelField $v)use(&$steps){
                /**
                 * @var FieldStepCollection $stepList
                 */
                $stepList=$v->steps();
                $stepList&&$stepList->each(function (FieldStep $step)use(&$steps){
                    isset($steps[$step->getStep()])||$steps[$step->getStep()]=$step->getTitle();
                });
            });

            $nextStepFieldName=$this->model::getNextStepField();
            if($nextStepFieldName&&in_array($nextStepFieldName,$this->model::getTableFields())){
                $nextStepField=SelectField::init($nextStepFieldName,'下一个步骤')->multiple(true)->items($steps);
                $nextStepField->filter()->multiple(true);
                $nextStepField->pushFieldDo()->setIndexFilterBeforeDo(function (ModelField $field,Query $query,array &$filterData){
                    $nextStepFieldName=$field->name();
                    if(empty($filterData[$nextStepFieldName])){
                        return;
                    }
                    $val=$filterData[$nextStepFieldName];
                    unset($filterData[$nextStepFieldName]);
                    is_array($val)||$val=[$val];
                    $query->whereIn($nextStepFieldName,$val);
                });

                $filterFields->unshift($nextStepField);
            }



            if($this->model::hasCurrentStepField()){
                $stepNameIsCurrentStep=true;
                $stepFieldName=$this->model::getCurrentStepField();
            }else{
                $stepNameIsCurrentStep=false;
                $stepFieldName=$this->model::getStepField();
            }


            $stepField=SelectField::init($stepFieldName,'当前步骤')->multiple(true)->items($steps);
            $stepField->filter()->multiple(true);
            $stepField->pushFieldDo()->setIndexFilterBeforeDo(function (ModelField $field,Query $query,array &$filterData)use($stepNameIsCurrentStep){
                $stepFieldName=$field->name();
                if(empty($filterData[$stepFieldName])){
                    return;
                }
                $val=$filterData[$stepFieldName];
                unset($filterData[$stepFieldName]);

                is_array($val)||$val=[$val];

                if($stepNameIsCurrentStep){
                    $query->whereIn($stepFieldName,$val);
                }else{
                    $sqls=[];
                    foreach ($val as $stepVal){
                        $stepVal=str_replace(["'",'\\','"'],'',$stepVal);
                        $sqls[]="JSON_EXTRACT(`$stepFieldName`,CONCAT(\"$[\",JSON_LENGTH(`$stepFieldName` ->> '$')-1,\"].step\"))='$stepVal'";
                    }
                    $query->whereRaw(implode(" OR ",$sqls));
                }
            });

            $filterFields->unshift($stepField);
        }
    }

    /**
     * @param FieldCollection $filterFields 筛选相关字段
     * @param array|null $filterData    筛选默认值
     * @param bool $showFilter  是否显示筛选
     * @return void
     */
    protected function indexFilterBefore(FieldCollection &$filterFields,?array &$filterData,bool &$showFilter):void{
        //index筛选显示前
    }
}