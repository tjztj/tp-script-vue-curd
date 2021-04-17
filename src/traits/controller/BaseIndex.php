<?php


namespace tpScriptVueCurd\traits\controller;


use think\db\Query;
use think\Request;
use tpScriptVueCurd\base\controller\BaseChildController;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\option\FunControllerIndexData;
use tpScriptVueCurd\option\FunControllerIndexPage;

/**
 * 为了方便有时候子控制器也使用
 * Trait BaseIndex
 * @property Request $request
 * @property FunControllerIndexPage $indexPageOption
 * @property FieldCollection $fields
 * @property VueCurlModel $model
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
        if($this->request->isAjax()){

            $model=$this->model
                ->where(function (Query $query){
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
                ->order($this->getListOrder());



            $doSteps=function(VueCurlModel $info){
                if(!$this->fields->stepIsEnable()){
                    return $info;
                }

                $stepInfo=$this->fields->getCurrentStepInfo($info);
                $stepInfo === null ||$stepInfo=clone $stepInfo;
                $info->stepInfo=$stepInfo?$stepInfo->listRowDo($info,null,$this->fields)->toArray():null;

                $nextStepInfo=$this->fields->getNextStepInfo($info);
                $nextStepInfo === null || $nextStepInfo=clone $nextStepInfo;
                $info->nextStepInfo=$nextStepInfo?$nextStepInfo->toArray():null;
                $info->stepNextCanEdit= $info->nextStepInfo && $nextStepInfo->authCheck($info, null, $this->fields->getFilterStepFields($nextStepInfo, true, $info));


                $stepFields=$stepInfo?$this->fields->getFilterStepFields($stepInfo,false,$info):FieldCollection::make();
                $info->stepFields=$stepFields->column('name');
                $info->stepCanEdit= $stepInfo && $stepInfo->authCheck($info, null, $stepFields);


                return $info;
            };


            $childListBtn=function(VueCurlModel $info){
                if($this->type()!=='base_have_child'){
                    return $info;
                }
                $childBtns=[];
                foreach (static::childControllerClassPathList() as $childControllerClass){
                    /* @var $childControllerClass BaseChildController|string */
                    $childBtns[class_basename($childControllerClass::modelClassPath())]=$childControllerClass::baseListBtnText($info);
                }
                $info->childBtns=$childBtns;
                return $info;
            };



            $option=new FunControllerIndexData();
            if($this->indexPageOption->pageSize>0){
                $pageData=$model->paginate($this->indexPageOption->canGetRequestOption?$this->request->param('pageSize/d',$this->indexPageOption->pageSize):$this->indexPageOption->pageSize)
                    ->map($doSteps)
                    ->map(fn(VueCurlModel $info)=>$info->rowSetAuth($this->fields,null,['show','edit','del']))
                    ->map($childListBtn)
                    ->toArray();
                $option->data=$pageData['data'];
                $option->currentPage=$pageData['current_page'];
                $option->lastPage=$pageData['last_page'];
                $option->perPage=$pageData['per_page'];
                $option->total=$pageData['total'];
            }else{
                $option->data=$model->select()
                    ->map($doSteps)
                    ->map(fn(VueCurlModel $info)=>$info->rowSetAuth($this->fields,null,['show','edit','del']))
                    ->map($childListBtn)
                    ->toArray();
            }

            foreach ($option->data as $k=>$v){
                $this->fields->doShowData($option->data[$k]);
            }

            $this->indexData($option);

            return $this->success($option->toArray());
        }

        $listColumns=array_values($this->fields->listShowItems()->toArray());
        $showTableTool=$this->request->param('show_table_tool/d',1)===1;



        $data=$this->indexFetch([
            'model'=>static::modelClassPath(),
            'modelName'=>class_basename(static::modelClassPath()),
            'indexPageOption'=>$this->indexPageOption,
            'listColumns'=>$listColumns,
            'groupGroupColumns'=>$this->fields->groupItems? FieldCollection::groupListByItems($listColumns):null,//不管显示是不是一个组，只要groupItems有，列表就分组
            'editUrl'=>url('edit')->build(),
            'showUrl'=>url('show')->build(),
            'delUrl'=>url('del')->build(),
            'downExcelTplUrl'=>url('downExcelTpl')->build(),
            'importExcelTplUrl'=>url('importExcelTpl')->build(),
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
                'stepAdd'=>$this->getAuthAdd(),
                'rowAuthAdd'=>$this->model->checkRowAuth($this->getRowAuthAddFields(),null,'add')
            ],
            'fieldComponents'=>$this->fields->listShowItems()->getComponents('index'),
            'filterComponents'=>$this->fields->getFilterComponents(),
            'fieldStepConfig'=>$this->fields->getStepConfig(),
        ]);

        return $this->showTpl('index',$data);
    }
}