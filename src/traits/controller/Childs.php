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
    private $parentController=null;


    /**
     * 设置当前控制器的父控制器
     * @param Controller $parentController
     * @param bool $force 是否强制替换掉原有的父控制器
     * @return $this
     */
    public function setParentController($parentController,bool $force=true):self{
        if(is_null($this->parentController)||$force){
            $this->parentController=$parentController;
        }
        return $this;
    }

    /**
     * 获取当前控制器的父控制器
     * @return Controller|null
     */
    public function getParentController(){
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