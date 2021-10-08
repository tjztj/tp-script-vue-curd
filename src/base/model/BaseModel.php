<?php


namespace tpScriptVueCurd\base\model;



use Closure;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;
use tpScriptVueCurd\tool\ErrorCode;

/**
 * Class BaseModel
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\model
 */
abstract class BaseModel extends VueCurlModel
{


    /**
     * 生成模型对象
     * @param Controller $controller
     * @return BaseModel
     */
    public static function make($controller): BaseModel
    {
        return new static([],$controller);
    }


    protected function doSaveDataBefore(FieldCollection $fields,array &$postData,bool $isExcelDo,int $id,?BaseModel $parentInfo,BaseModel $beforeInfo):void{} //执行doSaveData前（钩子）
    protected function doSaveDataAfter(array &$saveData,int $id,?BaseModel $parentInfo,BaseModel $beforeInfo):void{} //执行doSaveData后（钩子）
    protected function onAddAfter(BaseModel $info,array $postData,?BaseModel $parentInfo): void{}//添加后钩子
    protected function onEditAfter(BaseModel $info,array $postData,?BaseModel $parentInfo,BaseModel $beforeInfo): void{}//修改后钩子
}