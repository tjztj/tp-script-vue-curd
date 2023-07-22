<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\BaseModel;

class FunControllerImportAfter
{
    /**
     * 新增成功后的数据对象，包含id
     * @var BaseModel
     */
    public BaseModel $saveObjects;

    public ?BaseModel $base=null;

    public array $all=[];
}