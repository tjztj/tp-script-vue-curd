<?php

namespace tpScriptVueCurd\traits\controller;


use think\Collection;
use tpScriptVueCurd\base\model\BaseChildModel;
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
        //设置父表信息
        $baseInfo=null;
        try{
            $this->childIndexBeforeSetBase($baseInfo);
        }catch (\Exception $e){
            $this->error($e);
        }

        if($this->request->isAjax()){//只返回list
            $model=$this->childIndexListModelWhere($this->model,$baseInfo);;
            $option=$this->indexListSelect($model);
            $list=$option->sourceList;

            $list=$this->childSetListDataStep($list,$baseInfo);
            $list=$this->childSetListDataRowAuth($list,$baseInfo);


            try{
                $this->fields->each(function (ModelField $v)use($list,$baseInfo){$v->onIndexList($list,$baseInfo);});
                //字段钩子
                FieldDo::doIndex($this->fields,$list,$baseInfo);
            }catch (\Exception $e){
                return $this->error($e);
            }


            //显示处理
            $option->data=$list->toArray();
            foreach ($option->data as $k=>$v){
                $this->fields->doShowData($option->data[$k]);
            }

            $option->baseInfo=$baseInfo;
            $this->indexData($option);
            $this->success($option->toArray());
        }



        try{
            //要改fields，可以直接在 indexShowBefore 里面$this->fields
            $this->indexShowBefore($baseInfo);

            $this->fields->each(function (ModelField $v)use($baseInfo){$v->onIndexShow($baseInfo);});
            //字段钩子触发
            FieldDo::doIndexShow($this->fields,$baseInfo,$this);
        }catch (\Exception $e){
            return $this->error($e);
        }


        //base显示的字段处理
        $info=$baseInfo->toArray();
        $this->baseFields->doShowData($info);//有些数据不允许直接展示

        $listColumns=array_values($this->fields
            ->listShowItems()
            ->filter(fn(ModelField $v)=>!in_array($v->name(),[$this->model::getRegionField(),$this->model::getRegionPidField()]))//隐藏地区
            ->toArray());


        //是否显示添加按钮
        try{
            $rowAuthAdd=$this->model->checkRowAuth($this->getRowAuthAddFields(),$baseInfo,'add');
        }catch (\Exception $e){
            $rowAuthAdd=false;
        }

        $data=[
            'vueCurdAction'=>'childList',
            'indexPageOption'=>$this->indexPageOption,
            'info'=>$info,
            'listColumns'=>$listColumns,
            'groupGroupColumns'=>$this->fields->groupItems?FieldCollection::groupListByItems($listColumns):null,//不管显示是不是一个组，只要groupItems有，列表就分组
            'editUrl'=>url('edit')->build(),
            'delUrl'=>url('del')->build(),
            'showUrl'=>url('show')->build(),
            'downExcelTplUrl'=>url('downExcelTpl',['base_id'=>$baseInfo->id])->build(),
            'importExcelTplUrl'=>url('importExcelTpl',['base_id'=>$baseInfo->id])->build(),
            'title'=>static::getTitle(),
            'canDel'=>true,
            'auth'=>[
                'add'=>true,
                'edit'=>true,
                'del'=>true,
                'importExcelTpl'=>true,
                'downExcelTpl'=>true,
                'stepAdd'=>$this->getAuthAdd($baseInfo),
                'rowAuthAdd'=>$rowAuthAdd
            ],
            'fieldComponents'=>$this->fields->listShowItems()->getComponents('index'),
            'filterComponents'=>$this->fields->getFilterComponents(),
            'fieldStepConfig'=>$this->fields->getStepConfig(),
        ];

        try{
            $this->indexFetch($data);
        }catch (\Exception $e){
            $this->error($e);
        }
        return $this->showTpl('child_list',$data);
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
        $this->success($data);
    }


    /**
     * #title 子表数据添加修改
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(){
        return $this->editFields($this->fields,$this->model,$this->baseModel);
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
        $this->success('删除成功');
    }



    /**
     * BaseIndex 的列表中对$baseInfo赋值的逻辑
     * @param BaseModel|null $baseInfo
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function childIndexBeforeSetBase(?BaseModel &$baseInfo): void
    {
        if($baseInfo){
            //如果有了，就不设置了
            return;
        }
        $base_id=$this->request->param('base_id/d',0);
        if(empty($base_id)){
            throw new \think\Exception('缺少必要参数 base_id');
        }

        $baseInfo=$this->baseModel->find($base_id);
        if(empty($baseInfo)){
            throw new \think\Exception('未找到相关父表信息');
        }
    }

    /**
     * 列表显示的where条件处理
     * @param BaseChildModel $model
     * @param BaseModel $baseInfo
     * @return BaseChildModel
     */
    protected function childIndexListModelWhere(BaseChildModel $model,BaseModel $baseInfo,bool $canSearchId=true){
        return $model
            ->where(function($query)use($canSearchId){
                if($canSearchId){
                    $id=$this->request->param('id/d');
                    empty($id)||$query->where('id',$id);
                }
            })
            ->where($this->model::parentField(),$baseInfo->id)
            ->where(function (Query $query){
                $this->indexListWhere($query);
            })
            ->where($this->fields->getFilterWhere())
            ->where($this->stepAuthWhere($this->fields));
    }

    /**
     * 设置列表数据步骤信息
     * @param Collection|\think\model\Collection $list
     * @param BaseModel $baseInfo
     * @return Collection|\think\model\Collection
     */
    protected function childSetListDataStep($list,BaseModel $baseInfo){
        return $list->map(function(VueCurlModel $info)use($baseInfo){
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
        });
    }

    /**
     * 设置数据 __auth 的值，是否具有增删改的权限
     * @param Collection|\think\model\Collection $list
     * @param BaseModel $baseInfo
     * @return Collection|\think\model\Collection
     */
    protected function childSetListDataRowAuth($list,BaseModel $baseInfo){
        return $list->map(fn(VueCurlModel $info)=>$info->rowSetAuth($this->fields,$baseInfo,['show','edit','del']));
    }
}