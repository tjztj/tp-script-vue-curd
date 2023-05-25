<?php


namespace tpScriptVueCurd\option;


class ModelInfoAuth
{



    /**
     * 查看权限判断
     * function($fields, $info, $parentInfo){return true;}
     * @var callable $show
     */
    public $show;


    /**
     * 添加权限判断（为了方便与其他写在一起）
     * function($fields, $info, $parentInfo){return true;}
     * @var callable $add
     */
    public $add;

    /**
     * 编辑权限判断
     * function($fields, $info, $parentInfo){return true;}
     * @var callable $edit
     */
    public $edit;



    /**
     * 删除权限判断
     * function($fields, $info, $parentInfo){return true;}
     * @var callable $del
     */
    public $del;

}