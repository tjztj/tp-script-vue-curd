<?php

namespace tpScriptVueCurd\traits\controller;

use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\ErrorCode;

/**
 * @property BaseModel $md
 */
trait HaveChilds
{
    /**
     * @var Controller[]
     */
    private array $childControllers;


    /**
     * 是否显示子表的筛选
     * @var bool
     */
    public bool $showChildFilters=true;


    /**
     * 设置当前控制器的子控制器集合
     * @param array|Controller[] $childControllers
     * @return $this
     */
    final public function setChildControllers(array $childControllers):self{
        foreach ($childControllers as $k=>$v){
            $childControllers[$k]->setParentController($this);
        }
        $this->childControllers=$childControllers;
        return $this;
    }

    protected function childControllers():array{
        return [];
    }

    /**
     * 获取当前控制器的子控制器集合
     * @return array|Controller[]
     */
    final public function getChildControllers():array{
        if(!isset($this->childControllers)){
            $this->childControllers=$this->childControllers();
        }
        return $this->childControllers;
    }



    /**
     * 获取子表模型对象实例
     * @return BaseModel[]
     */
    public function getChildModelObjs():array{
        static $models=[];
        if(empty($models)){
            //子表
            foreach ($this->getChildControllers() as $childController){
                $models[get_class($childController)]=$childController->md;
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
        foreach ($this->getChildControllers() as $childController){
            /* @var $childController Controller */
            /* @var $childModel BaseModel */
            $childModelClass=get_class($childController->md);
            $childModel=$childController->md;
            $name=class_basename($childModelClass);
            $filterFields=$childModel->fields()->filter(fn(ModelField $v)=>$this->showChildFilters&&$childController->parentShowSelfFilter);


            $fetch['childs'][]=[
                'class'=>$childModelClass,
                'name'=>$name,
                'filterData'=>$this->showChildFilters&&$childController->parentShowSelfFilter?json_decode($this->request->param($name.'filterData','',null)):'',
                'title'=>$childController->title,
                'filterConfig'=>$filterFields->getFilterShowData(),
            ];
            $filterComponents += $filterFields->getFilterComponents();
        }
        $fetch['filterComponents']=$filterComponents;
        $fetch['deleteHaveChildErrorCode']=ErrorCode::DELETE_HAVE_CHILD;
    }
}