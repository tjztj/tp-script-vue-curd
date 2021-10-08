<?php

namespace tpScriptVueCurd\traits\controller;

use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\ErrorCode;

trait HaveChilds
{
    /**
     * @var Controller[]
     */
    public array $childControllers=[];


    /**
     * 获取子表模型对象实例
     * @return BaseModel[]
     */
    public function getChildModelObjs():array{
        static $models=[];
        if(empty($models)){
            //子表
            foreach ($this->childControllers as $childController){
                $modelClass=class_basename($childController->model);
                $models[get_class($childController)]=new $modelClass();
            }
        }
        return $models;
    }


    /**
     * index页面显示的数据处理
     * @param array $fetch
     * @return void
     */
    protected function indexFetchDoChild(array &$fetch):void{
        $filterComponents=$fetch['filterComponents']??[];

        $fetch['childs']=[];
        foreach ($this->childControllers as $childController){
            /* @var $childController Controller */
            /* @var $childModel BaseModel */
            $childModelClass=class_basename($childController->model);
            $childModel=new $childModelClass;
            $name=class_basename($childModelClass);
            $filterFields=$childModel->fields()->filter(fn(ModelField $v)=>$v->name()!==$childModel::getRegionField()&&$v->name()!==$childModel::getRegionPidField());


            $fetch['childs'][]=[
                'class'=>$childModelClass,
                'name'=>$name,
                'filterData'=>json_decode($this->request->param($name.'filterData','',null)),
                'title'=>$childController->title,
                'filterConfig'=>$filterFields->getFilterShowData(),
            ];
            $filterComponents += $filterFields->getFilterComponents();
        }
        $fetch['filterComponents']=$filterComponents;
        $fetch['deleteHaveChildErrorCode']=ErrorCode::DELETE_HAVE_CHILD;
    }
}