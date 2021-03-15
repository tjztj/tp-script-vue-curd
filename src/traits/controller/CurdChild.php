<?php

namespace tpScriptVueCurd\traits\controller;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use think\db\Query;
use think\Request;
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
        $info=$info->toArray();
        $this->baseFields->doShowData($info);//有些数据不允许直接展示

        $listColumns=array_values($this->fields
            ->listShowItems()
            ->filter(fn(ModelField $v)=>!in_array($v->name(),[$this->model::getRegionField(),$this->model::getRegionPidField()]))//隐藏地区
            ->toArray());

        return $this->showTpl('child_list',$this->indexFetch([
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
                'edit'=>true,
                'del'=>true,
                'importExcelTpl'=>true,
                'downExcelTpl'=>true,
            ],
            'fieldComponents'=>$this->getComponentsByFields($this->fields->listShowItems(),'index'),
            'filterComponents'=>$this->getFilterCommonentsByFields($this->fields),
        ]));
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
            ->where(function (Query $query){$this->indexListWhere($query);})
            ->where($this->fields->getFilterWhere());

        $option=new FunControllerIndexData();
        if($this->indexPageOption->pageSize>0){
            $pageData=$model->paginate($this->indexPageOption->canGetRequestOption?$this->request->param('pageSize/d',$this->indexPageOption->pageSize):$this->indexPageOption->pageSize)->toArray();
            $option->data=$pageData['data'];
            $option->currentPage=$pageData['current_page'];
            $option->lastPage=$pageData['last_page'];
            $option->perPage=$pageData['per_page'];
            $option->total=$pageData['total'];
        }else{
            $option->data=$model->select()->toArray();
        }
        foreach ($option->data as $k=>$v){
            $this->fields->doShowData($option->data[$k]);
        }

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
            try{
                if(empty($data['id'])){
                    if(empty($data[$this->model::parentField()])){
                        throw new \think\Exception('缺少关键信息');
                    }
                    $baseInfo=$this->baseModel->find($data[$this->model::parentField()]);
                    if(is_null($baseInfo)){
                        throw new \think\Exception('未找到所属数据');
                    }
                    $this->addAfter($this->model->addInfo($data,$baseInfo));
                }else{
                    $fields=$this->model->fields()->filter(fn(ModelField $v)=>!in_array($v->name(),[$this->model::getRegionField(),$this->model::getRegionPidField()]));//隐藏地区
                    $info=$this->model->find($data['id']);
                    $this->editAfter($this->model->saveInfo($data,$fields,$this->baseModel->find($info[$this->model::parentField()]),$info));
                }
            }catch (\Exception $e){
                $this->model->rollback();
                $this->errorAndCode($e->getMessage(),$e->getCode());
            }
            $this->model->commit();
            $this->success((empty($data['id'])?'添加':'修改').'成功');
        }

        $id=$this->request->param('id/d');
        if($id){
            $info=$this->model->find($id);
            $base_id=$info[$this->model::parentField()];
        }else{
            $base_id=$this->request->param('base_id/d',0);
            $base_id||$this->errorAndCode('缺少必要参数');
            $info=null;
        }

        $fields=$this->fields->filter(fn(ModelField $v)=>!in_array($v->name(),[$this->model::getRegionField(),$this->model::getRegionPidField()]));//不编辑地区
        $this->createEditFetchDataBefore($fields,$info);//切面
        $fetchData=$this->createEditFetchData($fields,$info);//切面
        $fetchData['baseId']=$base_id;
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
            return $this->errorAndCode($e->getMessage(),$e->getCode());
        }
        $this->model->commit();
        return $this->success('删除成功');
    }
}