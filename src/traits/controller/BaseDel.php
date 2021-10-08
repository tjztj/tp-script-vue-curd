<?php

namespace tpScriptVueCurd\traits\controller;

use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;

trait BaseDel
{
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

            if($this->childControllers&&$this->request->param('delChilds/d')==1){
                //先删除子数据再删除数据
                foreach ($this->childControllers as $v){
                    /**
                     * @var Controller $v
                     */
                    $childIds=[];
                    $parentField=$v->model::parentField();
                    (clone $v->model)->where($parentField,'in',$ids)->field('id,'.$parentField)->select()->each(function($val)use(&$childIds,$parentField){
                        isset($childIds[$val[$parentField]])||$childIds[$val[$parentField]]=[];
                        $childIds[$val[$parentField]][]=$val->id;
                    });

                    foreach ($childIds as $val){
                        $v->doDelect(clone $v->model,$val);
                    }
                }
            }
            $this->doDelect(clone $this->model,$ids);
        }catch (\Exception $e){
            $this->model->rollback();
            return $this->error($e);
        }

        $this->model->commit();
        $this->success('删除成功');
    }


    /**
     * 删除时
     * @param BaseModel $model
     * @param array $ids
     * @return \think\response\Json|void
     */
    public function doDelect(BaseModel $model,array $ids){
        $ids=$this->beforeDel($ids);
        $list= $model->del($ids);
        $this->afterDel($list);
    }
}