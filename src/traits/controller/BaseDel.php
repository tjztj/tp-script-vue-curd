<?php

namespace tpScriptVueCurd\traits\controller;

use think\Exception;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;

/**
 * @property BaseModel $md
 */
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
        $this->md->startTrans();
        try{
            $childControllers=$this->getChildControllers();
            if($childControllers&&$this->request->param('delChilds/d')==1){
                //先删除子数据再删除数据
                foreach ($childControllers as $v){
                    /**
                     * @var Controller $v
                     */
                    $childIds=[];
                    $parentField=$v->md::parentField();
                    (clone $v->md)->where($parentField,'in',$ids)->field('id,'.$parentField)->select()->each(function($val)use(&$childIds,$parentField){
                        isset($childIds[$val[$parentField]])||$childIds[$val[$parentField]]=[];
                        $childIds[$val[$parentField]][]=$val->id;
                    });

                    foreach ($childIds as $val){
                        $v->doDelect(clone $v->md,$val);
                    }
                }
            }
            $this->doDelect(clone $this->md,$ids);
        }catch (\Exception $e){
            $this->md->rollback();
            return $this->error($e);
        }

        $this->md->commit();
        $this->success('删除成功');
    }


    /**
     * 删除时
     * @param BaseModel $model
     * @param array $ids
     * @return \think\response\Json|void
     */
    public function doDelect(BaseModel $model,array $ids){
        if($this->treePidField){
            $childId=$model->whereIn($this->treePidField,$ids)->value('id');
            if($childId){
                if(count($ids)>1){
                    throw new Exception('请先删除第'.(array_search((string)$childId, array_map('strval',$ids), true) +1).'条数据的下级数据');
                }else{
                    throw new Exception('请先删除下级数据');
                }
            }
        }
        $ids=$this->beforeDel($ids);
        $list= $model->del($ids);
        $this->afterDel($list);
    }
}