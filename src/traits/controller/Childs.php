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
     * -- 是否在父列表中，判断当前用户有此表的添加权限 --
     * @var bool $parentIndexShowAddAuth 可能有性能问题，所以这个功能默认关闭
     */
    public bool $parentIndexShowAddAuth=false;


    /**
     * 父页面中是否显示此控制器的字段
     * @var bool
     */
    public bool $parentShowSelfFilter=true;
    /**
     * 父页面中是否要导入此控制器的数据
     * @var bool
     */
    public bool $parentImportSelf=true;

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