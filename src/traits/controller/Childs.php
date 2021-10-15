<?php

namespace tpScriptVueCurd\traits\controller;



use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\option\FunControllerListChildBtn;

trait Childs
{
    /**
     * @var Controller|null $parentController
     */
    private $parentController;


    /**
     * 设置当前控制器的父控制器
     * @param Controller $parentController
     * @return $this
     */
    final public function setParentController($parentController):self{
        $this->parentController=$parentController;
        return $this;
    }

    /**
     * 父控制器
     * @return object|null
     */
    protected function parentController():?object{
        return null;
    }

    /**
     * 获取当前控制器的父控制器
     * @return Controller|null
     */
    final public function getParentController(){
        if(!isset($this->parentController)){
            $this->parentController=$this->parentController();
        }
        return $this->parentController;
    }




    /**
     * 子表在主表列表中的按钮文字，可重写
     * @param FunControllerListChildBtn $btn
     * @param BaseModel $info
     * @return void
     */
    public function baseListBtnText(FunControllerListChildBtn $btn,BaseModel $info):void{
        $btn->color='#d46b08';
        $btn->text='详细列表';
    }
}