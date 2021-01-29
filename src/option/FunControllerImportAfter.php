<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\VueCurlModel;

class FunControllerImportAfter
{
    /**
     * 新增成功后的数据对象，包含id
     * @var VueCurlModel
     */
    public VueCurlModel $saveObjects;
}