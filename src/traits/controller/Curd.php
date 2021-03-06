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
    use BaseIndex;//为了方便有时候子控制器也使用
    public VueCurlModel $model;
    public FieldCollection $fields;





    /**
     * #title 添加与修改
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(){
        return $this->editFields();
    }




    /**
     * #title 删除数据
     */
    public function del(){
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
            return $this->error($e);
        }

        $this->model->commit();
        $this->success('删除成功');
    }
}