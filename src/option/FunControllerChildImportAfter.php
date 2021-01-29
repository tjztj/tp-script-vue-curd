<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;

class FunControllerChildImportAfter
{
    /**
     * 新增成功后的数据对象，包含id
     * @var VueCurlModel
     */
    public VueCurlModel $saveObjects;


    /**
     * 新增数据的父表数据对象
     * @var BaseModel
     */
    public BaseModel $base;
}