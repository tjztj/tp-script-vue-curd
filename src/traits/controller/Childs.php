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
    public $parentController=null;



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