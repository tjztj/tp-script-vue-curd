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
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
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
        if(is_null($baseInfo)&&isset($this->baseModel)&&!is_null($this->baseModel)){
            $baseId=$this->request->param('base_id/d',0);
            if($baseId){
                $baseInfo=$this->baseModel->find($baseId);
                if(!$baseInfo){
                    return $this->error('未能获取到相关父表信息');
                }
            }
        }

        if($this->request->isAjax()){
            $model=$this->model
                ->where(function (Query $query)use($baseInfo){
                    if($baseInfo){
                        $query->where($this->model::parentField(),$baseInfo->id);
                    }
                    $id=$this->request->param('id/d');
                    if($id){
                        $query->where('id',$id);
                    }
                    $this->indexListWhere($query);
                })
                ->where($this->fields->getFilterWhere())
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
                ->where($this->stepAuthWhere())
                ->order($this->getListOrder());






            $option=new FunControllerIndexData();
            $option->model=clone $model;
            if($this->indexPageOption->pageSize>0){
                $list=$model->paginate($this->indexPageOption->canGetRequestOption?$this->request->param('pageSize/d',$this->indexPageOption->pageSize):$this->indexPageOption->pageSize);
                $option->currentPage=$list->currentPage();
                $option->lastPage=$list->lastPage();
                $option->perPage=$list->listRows();
                $option->total=$list->total();
                $list=$list->getCollection();
            }else{
                $list=$model->select();
                $option->total=$list->count();
                $option->currentPage=1;
                $option->lastPage=1;
                $option->perPage=$option->total;
            }


            if(is_null($baseInfo)&&isset($this->baseModel)&&!is_null($this->baseModel)&&!$list->isEmpty()){
                $baseInfo=[];
                foreach ($list as $v){
                    $baseInfo[$v[$this->model::parentField()]]=null;
                }
                foreach ($this->baseModel->where('id','in',array_keys($baseInfo))->select() as $val){
                    $baseInfo[$val->id]=$val;
                }
            }


            $doSteps=function(VueCurlModel $info)use($baseInfo){
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
            };

            $childListBtn=function(VueCurlModel $info){
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
            };

            $list->map($doSteps)
                ->map(fn(VueCurlModel $info)=>$info->rowSetAuth($this->fields,is_array($baseInfo)?($baseInfo[$info[$this->model::parentField()]]??null):$baseInfo,['show','edit','del']))
                ->map($childListBtn);


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
                return $this->error($e);
            }


            $option->data=$list->toArray();
            foreach ($option->data as $k=>$v){
                $this->fields->doShowData($option->data[$k]);
            }
            $option->baseInfo=is_array($baseInfo)?null:$baseInfo;

            $this->indexData($option);

            return $this->success($option->toArray());
        }


        try{
            FieldDo::doIndexShow($this->fields,$baseInfo,$this);
        }catch (\Exception $e){
            return $this->error($e);
        }

        $listColumns=array_values($this->fields->listShowItems()->toArray());
        $showTableTool=$this->request->param('show_table_tool/d',1)===1;



        try{
            $rowAuthAdd=$this->model->checkRowAuth($this->getRowAuthAddFields(),$baseInfo,'add');
        }catch (\Exception $e){
            $rowAuthAdd=false;
        }


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
            'filterConfig'=>$this->fields->getFilterShowData(),
            'filter_data'=>json_decode($this->request->param('filter_data','',null)),
            'showFilter'=>$this->request->param('show_filter/d',1)===1,
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
            'filterComponents'=>$this->fields->getFilterComponents(),
            'fieldStepConfig'=>$this->fields->getStepConfig(),
        ];

        if($this->type()==='base_have_child'){
            $this->indexFetchDoChild($data);
        }

        $this->indexFetch($data);
        return $this->showTpl('index',$data);
    }
}