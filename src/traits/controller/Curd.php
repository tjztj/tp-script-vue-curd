<?php


namespace tpScriptVueCurd\traits\controller;


use think\Exception;
use tpScriptVueCurd\base\controller\BaseChildController;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use think\db\Query;
use think\Request;
use tpScriptVueCurd\option\FunControllerIndexData;
use tpScriptVueCurd\option\FunControllerIndexPage;

/**
 * Trait Curd
 * @property Request $request
 * @property FunControllerIndexPage $indexPageOption
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait Curd
{
    public VueCurlModel $model;
    public FieldCollection $fields;




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
                $info->stepInfo=$stepInfo?$stepInfo->toArray():null;

                $nextStepInfo=$this->fields->getNextStepInfo($info);
                $info->nextStepInfo=$nextStepInfo?$nextStepInfo->toArray():null;

                $info->stepFields=$stepInfo?$this->fields->getFilterStepFields($stepInfo,false,$info)->column('name'):FieldCollection::make();

                $info->stepCanEdit=$stepInfo?$stepInfo->authCheck($info,null,$info->stepFields):false;

                return $info;
            };

            $option=new FunControllerIndexData();
            if($this->indexPageOption->pageSize>0){
                $pageData=$model->paginate($this->indexPageOption->canGetRequestOption?$this->request->param('pageSize/d',$this->indexPageOption->pageSize):$this->indexPageOption->pageSize)->map($doSteps)->toArray();
                $option->data=$pageData['data'];
                $option->currentPage=$pageData['current_page'];
                $option->lastPage=$pageData['last_page'];
                $option->perPage=$pageData['per_page'];
                $option->total=$pageData['total'];
            }else{
                $option->data=$model->select()->map($doSteps)->toArray();
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
            ],
            'fieldComponents'=>$this->fields->listShowItems()->getComponents('index'),
            'filterComponents'=>$this->fields->getFilterComponents(),
            'fieldStepConfig'=>$this->fields->getStepConfig(),
        ]);

        return $this->showTpl('index',$data);
    }


    /**
     * #title 添加与修改
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function edit(){
        if($this->request->isAjax()){
            $data=$this->request->post();
            $this->model->startTrans();
            $savedInfo=null;
            try{
                if(empty($data['id'])){
                    $this->addBefore($data);
                    //步骤字段
                    $this->fields=$this->fields->filterNextStepFields(null,null,$stepInfo);
                    $this->fields->saveStepInfo=$stepInfo;

                    //步骤权限验证
                    if($this->fields->saveStepInfo&&$this->fields->saveStepInfo->authCheck(null,null,$this->fields)===false){
                        return $this->error('您不能进行此操作');
                    }

                    $savedInfo=$this->model->addInfo($data,$this->fields);
                    $this->addAfter($savedInfo);
                }else{
                    $old=$this->model->find($data['id']);

                    $this->editBefore($data,$old);

                    //步骤
                    $this->fields=$this->getSaveStepNext()
                        ?$this->fields->filterNextStepFields($old,null,$stepInfo)
                        :$this->fields->filterCurrentStepFields($old,null,$stepInfo);
                    $this->fields->saveStepInfo=$stepInfo;

                    //步骤权限验证
                    if($this->fields->saveStepInfo&&$this->fields->saveStepInfo->authCheck($old,null,$this->fields)===false){
                        return $this->error('您不能进行此操作');
                    }

                    $savedInfo=$this->model->saveInfo($data,$this->fields,null,$old);
                    $this->editAfter($savedInfo);
                }
            }catch (\Exception $e){
                $this->model->rollback();
                $this->errorAndCode($e->getMessage());
            }
            $this->model->commit();
            $this->success((empty($data['id'])?'添加':'修改').'成功',[
                'data'=>$data,
                'info'=>$savedInfo,
            ]);
        }

        $id=$this->request->param('id/d');

        $info=$id?$this->model->find($id):null;
        $this->createEditFetchDataBefore($this->fields,$info);
        $fetchData=$this->createEditFetchData($this->fields,$info);
        $fetchData=$id?$this->beforeEditShow($fetchData):$this->beforeAddShow($fetchData);

        return $this->showTpl('edit',$fetchData);
    }




    /**
     * #title 删除数据
     */
    function del(){
        $ids=$this->request->param('ids/a',[]);
        $ids=array_filter($ids);
        if(empty($ids)){
            return $this->error('请选择要删除的数据');
        }
        $this->model->startTrans();
        try{
            if(static::type()==='base_have_child'&&$this->request->param('delChilds/d')==1){
                //先删除子数据再删除数据
                foreach (static::childModelObjs() as $k=>$v){
                    $childIds=[];
                    /**
                     * @var BaseChildController|string $baseChildController
                     * @var BaseChildModel $v
                     */
                    $v->where($v::parentField(),'in',$ids)->field('id,'.$v::parentField())->select()->each(function($val)use(&$childIds,$v){
                        isset($childIds[$val[$v::parentField()]])||$childIds[$val[$v::parentField()]]=[];
                        $childIds[$val[$v::parentField()]][]=$val->id;
                    });
                    $baseChildController=new $k($this->app);
                    foreach ($childIds as $val){
                        $baseChildController->doDelect($v,$val);
                    }
                }
            }
            $this->doDelect($this->model,$ids);
        }catch (Exception $e){
            $this->model->rollback();
            return $this->errorAndCode($e->getMessage(),$e->getCode());
        }

        $this->model->commit();
        return $this->success('删除成功');
    }
}