<?php

namespace tpScriptVueCurd\traits\controller;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\field\FilesField;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use think\db\Query;
use think\Request;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FunControllerIndexData;
use tpScriptVueCurd\option\FunControllerIndexPage;

/**
 * Trait CurdChild
 * @property Request $request
 * @property FunControllerIndexPage $indexPageOption
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait CurdChild{

    public VueCurlModel $model;
    public BaseModel $baseModel;
    public FieldCollection $fields;
    public FieldCollection $baseFields;



    /**
     * #title 详细子列表页面
     * @return mixed|\think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        $base_id=$this->request->param('base_id/d',0);
        $base_id||$this->errorAndCode('缺少必要参数');


        if($this->request->isAjax()){//只返回list
            $listData=$this->getChildList($base_id);
            $this->indexData($listData);
            return $this->success($listData->toArray());
        }

        $info=$this->baseModel->find($base_id);
        $info||$this->errorAndCode('未找到相关信息');
        $baseInfo=$info;

        try{
            FieldDo::doIndexShow($this->fields,$baseInfo,$this);
        }catch (\Exception $e){
            return $this->error($e);
        }

        $info=$info->toArray();
        $this->baseFields->doShowData($info);//有些数据不允许直接展示

        $listColumns=array_values($this->fields
            ->listShowItems()
            ->filter(fn(ModelField $v)=>!in_array($v->name(),[$this->model::getRegionField(),$this->model::getRegionPidField()]))//隐藏地区
            ->toArray());


        $data=[
            'vueCurdAction'=>'childList',
            'indexPageOption'=>$this->indexPageOption,
            'info'=>$info,
            'listColumns'=>$listColumns,
            'groupGroupColumns'=>$this->fields->groupItems?FieldCollection::groupListByItems($listColumns):null,//不管显示是不是一个组，只要groupItems有，列表就分组
            'editUrl'=>url('edit')->build(),
            'delUrl'=>url('del')->build(),
            'showUrl'=>url('show')->build(),
            'downExcelTplUrl'=>url('downExcelTpl',['base_id'=>$base_id])->build(),
            'importExcelTplUrl'=>url('importExcelTpl',['base_id'=>$base_id])->build(),
            'title'=>static::getTitle(),
            'canDel'=>true,
            'auth'=>[
                'add'=>true,
                'edit'=>true,
                'del'=>true,
                'importExcelTpl'=>true,
                'downExcelTpl'=>true,
                'stepAdd'=>$this->getAuthAdd($baseInfo),
                'rowAuthAdd'=>$this->model->checkRowAuth($this->getRowAuthAddFields(),$baseInfo,'add')
            ],
            'fieldComponents'=>$this->fields->listShowItems()->getComponents('index'),
            'filterComponents'=>$this->fields->getFilterComponents(),
            'fieldStepConfig'=>$this->fields->getStepConfig(),
        ];
        $this->indexFetch($data);
        return $this->showTpl('child_list',$data);
    }


    /**
     * #title 获取子列表
     * @param int $base_id
     * @return FunControllerIndexData
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getChildList(int $base_id):FunControllerIndexData{
        $model=$this->model
            ->where($this->model::parentField(),$base_id)
            ->where(function($query){
                $id=$this->request->param('id/d');
                if($id){
                    $query->where('id',$id);
                }
            })
            ->where(function (Query $query){$this->indexListWhere($query);})
            ->where($this->fields->getFilterWhere())
            ->where($this->stepAuthWhere())
            ->order($this->getListOrder());

        $baseInfo=$this->baseModel->find($base_id);

        $doSteps=function(VueCurlModel $info)use($baseInfo){
            if(!$this->fields->stepIsEnable()){
                return $info;
            }

            $stepInfo=$this->fields->getCurrentStepInfo($info,$baseInfo);
            $stepInfo === null ||$stepInfo=clone $stepInfo;
            $info->stepInfo=$stepInfo?$stepInfo->listRowDo($info,$baseInfo,$this->fields)->toArray():null;

            $nextStepInfo=$this->fields->getNextStepInfo($info,$baseInfo);
            $nextStepInfo === null || $nextStepInfo=clone $nextStepInfo;
            $info->nextStepInfo=$nextStepInfo?$nextStepInfo->toArray():null;
            $info->stepNextCanEdit= $info->nextStepInfo && $nextStepInfo->authCheck($info, $baseInfo, $this->fields->getFilterStepFields($nextStepInfo, true, $info));

            $stepFields=$stepInfo?$this->fields->getFilterStepFields($stepInfo,false,$info,$baseInfo):FieldCollection::make();
            $info->stepFields=$stepFields->column('name');

            $info->stepCanEdit= $stepInfo && $stepInfo->authCheck($info, $baseInfo, $stepFields);

            return $info;
        };

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

        $list->map($doSteps)
            ->map(fn(VueCurlModel $info)=>$info->rowSetAuth($this->fields,$baseInfo,['show','edit','del']));

        try{
            //字段钩子
            FieldDo::doIndex($this->fields,$list,$baseInfo);
        }catch (\Exception $e){
            return $this->error($e);
        }


        $option->data=$list->toArray();
        foreach ($option->data as $k=>$v){
            $this->fields->doShowData($option->data[$k]);
        }

        $option->baseInfo=$baseInfo;
        return $option;
    }



    /**
     * #title 获取子表显示数据+其父表数据，权限同getChildList一样
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function getPagingList(): \think\response\Json
    {
        //获取所有的数据
        $model=$this->model;

        $isGetBase=$this->request->param('get_base/d',0);
        $isGetBase||$this->request->param('getBase/d',0);
        $isGetBase=$isGetBase===1;


        if($isGetBase){
            $model=$model->with(['baseModel']);
        }


        //base条件
        $baseWhere=function(Query $query)use($model){
            $base_where=$this->request->param('base_where',[],null);
            if(empty($base_where)){
                $base_where=$this->request->param('baseWhere',[],null);
                if(empty($base_where)){
                    return;
                }
            }
            if(is_string($base_where)){
                $base_where=json_decode($base_where,true);
            }

            if(isset($base_where['filterData'])){
                $base_where=$base_where['filterData'];
            }else if(isset($base_where['filter_data'])){
                $base_where=$base_where['filter_data'];
            }
            if(is_string($base_where)){
                $base_where=json_decode($base_where,true);
            }

            $query->whereRaw('`'.$model::parentField().'` IN '.$this->baseModel->field('id')->where($this->baseFields->getFilterWhere($base_where))->buildSql());
        };



        $data=$model->where($baseWhere)
            ->where($this->fields->getFilterWhere())
            ->paginate($this->request->param('pageSize/d',10))
            ->toArray();
        $list=&$data['data'];
        foreach ($list as $k=>$v){
            $this->fields->doShowData($list[$k]);

            if(!empty($list[$k]['baseModel'])){
                $this->baseFields->doShowData($list[$k]['baseModel']);
                foreach ($list[$k]['baseModel'] as $key=>$value){
                    $list[$k]['baseModel.'.$key]=$value;
                }
            }
        }
        return $this->success($data);
    }


    /**
     * #title 子表数据添加修改
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(){
        if($this->request->isAjax()){
            $data=$this->request->post();
            $this->model->startTrans();
            $savedInfo=null;
            $baseInfo=null;
            $info=null;
            $returnSaveData=[];
            try{
                if(empty($data['id'])){
                    if(empty($data[$this->model::parentField()])){
                        throw new \think\Exception('缺少关键信息');
                    }
                    $baseInfo=$this->baseModel->find($data[$this->model::parentField()]);
                    if(is_null($baseInfo)){
                        throw new \think\Exception('未找到所属数据');
                    }

                    $this->addBefore($data,$baseInfo);

                    //步骤字段
                    $this->fields=$this->fields->filterNextStepFields($info,$baseInfo,$stepInfo);
                    $this->fields->saveStepInfo=$stepInfo;

                    //步骤权限验证
                    if($this->fields->saveStepInfo&&$this->fields->saveStepInfo->authCheck($info,$baseInfo,$this->fields)===false){
                        return $this->error('您不能进行此操作-03');
                    }

                    $savedInfo=$this->model->addInfo($data,$baseInfo,$this->fields,false,$returnSaveData);
                    $this->addAfter($savedInfo);
                }else{
                    $info=$this->model->find($data['id']);
                    $baseInfo=$this->baseModel->find($info[$this->model::parentField()]);

                    $this->editBefore($data,$info,$baseInfo);

                    //步骤
                    $isNext=$this->autoGetSaveStepIsNext($this->fields,$info,$baseInfo);
                    if(is_null($isNext)){
                        return $this->error('数据不满足当前步骤');
                    }
                    if($isNext){
                        $this->fields=$this->fields->filterNextStepFields($info,$baseInfo,$stepInfo);
                    }else{
                        $this->fields=$this->fields->filterCurrentStepFields($info,$baseInfo,$stepInfo);
                        if(!$this->checkEditUrl($this->fields,$stepInfo)){
                            return $this->error('您不能进行此操作-06');
                        }
                    }
                    $this->fields->saveStepInfo=$stepInfo;

                    //步骤权限验证
                    if($this->fields->saveStepInfo&&$this->fields->saveStepInfo->authCheck($info,$baseInfo,$this->fields)===false){
                        return $this->error('您不能进行此操作-04');
                    }


                    $fields=$this->fields->filter(fn(ModelField $v)=>!in_array($v->name(),[$this->model::getRegionField(),$this->model::getRegionPidField()])||$v->canEdit()===false);//隐藏地区

                    $savedInfo=$this->model->saveInfo($data,$fields,$baseInfo,$info,$returnSaveData);
                    $this->editAfter($savedInfo);
                }
            }catch (\Exception $e){
                $this->model->rollback();
                $this->error($e);
            }
            $this->model->commit();


            //提交后
            $msg=(empty($data['id'])?'添加':($this->getSaveStepNext()?'提交':'修改')).'成功';
            $this->editCommitAfter($msg,$info,$savedInfo,$baseInfo,$returnSaveData);

            $this->success($msg,[
                'data'=>$data,
                'info'=>$savedInfo,
                'baseInfo'=>$baseInfo
            ]);
        }

        $id=$this->request->param('id/d');
        if($id){
            $info=$this->model->find($id);
            $base_id=$info[$this->model::parentField()];
            $baseInfo=$this->baseModel->find($info[$this->model::parentField()]);
        }else{
            $base_id=$this->request->param('base_id/d',0);
            $base_id||$this->errorAndCode('缺少必要参数');
            $info=null;
            $baseInfo=$this->baseModel->find($base_id);
        }

        $fields=$this->fields->filter(fn(ModelField $v)=>!in_array($v->name(),[$this->model::getRegionField(),$this->model::getRegionPidField()])||$v->canEdit()===false);//不编辑地区

        try{
            $this->createEditFetchDataBefore($fields,$info,$baseInfo);//切面
        }catch (\Exception $e){
            return $this->error($e);
        }


        $fetchData=$this->createEditFetchData($fields,$info,$baseInfo);//切面
        $fetchData['baseId']=$base_id;
        $fetchData['baseInfo']=$baseInfo;
        $fetchData['parentField']=$this->model::parentField();
        $fetchData['vueCurdAction']='childEdit';

        $fetchData=$id?$this->beforeEditShow($fetchData):$this->beforeAddShow($fetchData);

        return $this->showTpl('edit',$fetchData);
    }


    /**
     * #title 删除子表数据
     * @return \think\response\Json|void
     */
    function del(){
        $ids=$this->request->param('ids/a',[]);
        $ids=array_filter($ids);
        if(empty($ids)){
            return $this->error('请选择要删除的数据');
        }
        $this->model->startTrans();
        try{
            $this->doDelect($this->model,$ids);
        }catch (\Exception $e){
            $this->model->rollback();
            return $this->error($e);
        }
        $this->model->commit();
        return $this->success('删除成功');
    }
}