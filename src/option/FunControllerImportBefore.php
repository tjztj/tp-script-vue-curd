<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\BaseModel;

class FunControllerImportBefore
{
    /**
     * 执行保存前，用户给过来的数据
     * @var array
     */
    public array $saveArr=[];

    public ?BaseModel $base=null;

    public array $all=[];
}