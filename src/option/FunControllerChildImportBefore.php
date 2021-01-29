<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\BaseModel;

class FunControllerChildImportBefore
{

    /**
     * 执行保存前，用户给过来的数据
     * @var array
     */
    public array $saveArr=[];

    /**
     * 父模型数据对象
     * @var BaseModel
     */
    public BaseModel $base;
}