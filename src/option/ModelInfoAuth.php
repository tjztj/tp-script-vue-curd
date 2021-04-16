<?php


namespace tpScriptVueCurd\option;


class ModelInfoAuth
{



    /**
     * 查看权限判断
     * @var callable $show
     */
    public $show;


    /**
     * 编辑权限判断
     * @var callable $edit
     */
    public $edit;



    /**
     * 删除权限判断
     * @var callable $del
     */
    public $del;

}