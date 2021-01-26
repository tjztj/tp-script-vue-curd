<?php

namespace tpScriptVueCurd\traits\controller;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use think\db\Query;
use think\Request;

/**
 * Trait CurdChild
 * @property Request $request
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait CurdChild{

    public VueCurlModel $model;
    public BaseModel $baseModel;
    public FieldCollection $fields;
    public FieldCollection $baseFields;

    /**
     * 子表详细页面
     * @return mixed|void
     */
    public function show(){
        return $this->doShow($this->model,$this->fields);
    }


    /**
     * 详细子列表页面
     * @return mixed|\think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        $base_id=$this->request->param('base_id/d',0);
        $base_id||$this->error('缺少必要参数');

        $list=$this->getChildList($base_id);
        $list=$this->indexData($list);
        if($this->request->param('just_get_childs/d')===1){//只返回list
            return $this->success($list);
        }

        $info=$this->baseModel->find($base_id);
        $info||$this->error('未找到相关信息');
        $info=$info->toArray();
        $this->baseFields->doShowData($info);//有些数据不允许直接展示

        $listColumns=array_values($this->fields
            ->listShowItems()
            ->filter(fn(ModelField $v)=>!in_array($v->name(),['system_region_id','system_region_pid']))//隐藏地区
            ->toArray());

        return $this->showTpl('child_list',$this->indexFetch([
            'vueCurdAction'=>'childList',
            'info'=>$info,
            'list'=>$list,
            'listColumns'=>$listColumns,
            'groupGroupColumns'=>$this->fields->groupItems?FieldCollection::groupListByItems($listColumns):null,//不管显示是不是一个组，只要groupItems有，列表就分组
            'editUrl'=>url('edit')->build(),
            'delUrl'=>url('del')->build(),
            'showUrl'=>url('show')->build(),
            'downExcelTplUrl'=>url('downExcelTpl',['base_id'=>$base_id])->build(),
            'importExcelTplUrl'=>url('importExcelTpl',['base_id'=>$base_id])->build(),
            'title'=>$this->model::getTitle(),
            'canDel'=>true,
            'auth'=>[
                'edit'=>true,
                'del'=>true,
                'importExcelTpl'=>true,
                'downExcelTpl'=>true,
            ]
        ]));
    }


    /**
     * 获取子列表
     * @param int $base_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getChildList(int $base_id):array{
        $list=$this->model
            ->where('base_id',$base_id)
            ->where(function (Query $query){$this->indexListWhere($query);})
            ->where($this->fields->getFilterWhere())->select()->toArray();
        $list||$list=[];
        foreach ($list as $k=>$v){
            $this->fields->doShowData($list[$k]);
        }
        return $list;
    }



    /**
     * 获取子表显示数据+其父表数据，权限同getChildList一样
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
        $baseWhere=function(Query $query){
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

            $query->whereRaw('base_id IN '.$this->baseModel->field('id')->where($this->baseFields->getFilterWhere($base_where))->buildSql());
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
     * 子表数据添加修改
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
                    if(empty($data['base_id'])){
                        throw new \think\Exception('缺少关键信息');
                    }
                    $baseInfo=$this->baseModel->find($data['base_id']);
                    if(is_null($baseInfo)){
                        throw new \think\Exception('未找到所属数据');
                    }
                    $this->addAfter($this->model->addInfo($data,$baseInfo));
                }else{
                    $fields=$this->model->fields()->filter(fn(ModelField $v)=>$v->name()!=='system_region_id'&&$v->name()!=='system_region_pid');
                    $this->editAfter($this->model->saveInfo($data,$fields));
                }
            }catch (\Exception $e){
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            $this->model->commit();
            $this->success((empty($data['id'])?'添加':'修改').'成功');
        }

        $id=$this->request->param('id/d');
        if($id){
            $info=$this->model->find($id);
            $base_id=$info->base_id;
        }else{
            $base_id=$this->request->param('base_id/d',0);
            $base_id||$this->error('缺少必要参数');
            $info=null;
        }

        $fields=$this->fields->filter(fn(ModelField $v)=>!in_array($v->name(),['system_region_id','system_region_pid']));//不编辑地区
        $this->createEditFetchDataBefore($fields,$info);//切面
        $fetchData=$this->createEditFetchData($fields,$info);//切面
        $fetchData['baseId']=$base_id;
        $fetchData['vueCurdAction']='childEdit';

        $fetchData=$id?$this->beforeEditShow($fetchData):$this->beforeAddShow($fetchData);

        return $this->showTpl('edit',$fetchData);
    }


    /**
     * 删除子表数据
     * @return \think\response\Json|void
     */
    function del(){
        return $this->doDelect($this->model,$this->request->param('ids/a',[]));
    }
}