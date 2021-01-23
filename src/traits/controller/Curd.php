<?php


namespace tpScriptVueCurd\traits\controller;


use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use think\db\Query;
use think\Request;

/**
 * Trait Curd
 * @property Request $request
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait Curd
{
    public VueCurlModel $model;
    public FieldCollection $fields;


    /**
     * @NodeAnotation(title="数据列表")
     * @return mixed|\think\response\Json|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function index(){
        if($this->request->isAjax()){
            if($this->request->param('sortField')){
                $order=$this->request->param('sortField').' '.$this->request->param('sortOrder');
            }else{
                $order='id DESC';
            }
            $list=$this->model
                ->where(function (Query $query){
                    $this->indexListWhere($query);
                })
                ->where($this->fields->getFilterWhere())
                ->order($order)->paginate($this->request->param('pageSize/d',10))->toArray();
            foreach ($list['data'] as $k=>$v){
                $this->fields->doShowData($list['data'][$k]);
            }
            return $this->success($this->indexData($list));
        }

        $listColumns=array_values($this->fields->listShowItems()->toArray());

        $paths=array_filter(explode('/',str_replace('\\', '/', static::class)));
        $tolDir=$paths[count($paths)-1].'/'.parse_name(class_basename(static::class));
        $showTableTool=$this->request->param('show_table_tool/d',1)===1;



        return $this->fetch(file_exists(app_path('view/'.$tolDir).'index.vue')?$tolDir.'/index':'vuecurd/index'
            ,$this->indexFetch([
                'model'=>static::modelClassPath(),
                'modelName'=>class_basename(static::modelClassPath()),
                'listColumns'=>$listColumns,
                'groupGroupColumns'=>$this->fields->groupItems? FieldCollection::groupListByItems($listColumns):null,//不管显示是不是一个组，只要groupItems有，列表就分组
                'editUrl'=>url('edit')->build(),
                'showUrl'=>url('show')->build(),
                'delUrl'=>url('del')->build(),
                'downExcelTplUrl'=>url('downExcelTpl')->build(),
                'importExcelTplUrl'=>url('importExcelTpl')->build(),
                'title'=>$this->model::getTitle(),
                'childs'=>[],//会在BaseHaveChildController中更改
                'filterConfig'=>$this->fields->getFilterShowData(),
                'filter_data'=>json_decode($this->request->param('filter_data','',null)),
                'showFilter'=>$this->request->param('show_filter/d',1)===1,
                'showTableTool'=>$showTableTool,
                'canEdit'=>$showTableTool,
                'canDel'=>$showTableTool,
                'auth'=>[
                    'edit'=>true,
                    'del'=>true,
                    'importExcelTpl'=>true,
                    'downExcelTpl'=>true,
                ]
            ]));
    }


    /**
     * @NodeAnotation(title="添加与修改")
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
            try{
                if(empty($data['id'])){
                    $this->addAfter($this->model->addInfo($data));
                }else{
                    $this->editAfter($this->model->saveInfo($data));
                }
            }catch (\Exception $e){
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            $this->model->commit();
            $this->success((empty($data['id'])?'添加':'修改').'成功');
        }

        $id=$this->request->param('id/d');

        $info=$id?$this->model->find($id):null;
        $this->createEditFetchDataBefore($this->fields,$info);
        $fetchData=$this->createEditFetchData($this->fields,$info);
        $fetchData=$id?$this->beforeEditShow($fetchData):$this->beforeAddShow($fetchData);

        return $this->fetch('vuecurd/edit',$fetchData);
    }



    /**
     *  @NodeAnotation(title="详细页面")
     * @return mixed|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function show(){
       return $this->doShow($this->model,$this->fields);
    }



    /**
     * @NodeAnotation(title="删除数据")
     */
    function del(){
        return $this->doDelect($this->model,$this->request->param('ids/a',[]));
    }
}