<?php


namespace tpScriptVueCurd\base\model;


use tpScriptVueCurd\base\controller\BaseChildController;
use tpScriptVueCurd\base\controller\BaseController;
use tpScriptVueCurd\base\controller\BaseHaveChildController;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\traits\model\GenerateTable;
use tpScriptVueCurd\traits\model\InfoAuth;
use tpScriptVueCurd\traits\model\ModelBaseField;
use tpScriptVueCurd\traits\model\ModelDelTraits;
use tpScriptVueCurd\traits\model\ModelSave;
use tpScriptVueCurd\traits\model\ModelStep;

/**
 * Class VueCurlModel
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\model
 */
abstract class VueCurlModel extends TimeModel
{
    use ModelSave,ModelDelTraits,ModelBaseField,ModelStep,InfoAuth,GenerateTable;


    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->doGenerateTable();
    }


    abstract public function fields():FieldCollection;

    /**
     * 相关控制器
     * @return string|BaseController|BaseHaveChildController|BaseChildController
     */
    abstract public static function getControllerClass():string;


    /**
     * 时候有村社字段，如果有，固定（region_id,region_pid）
     * @return bool
     */
    public static function haveRegionField():bool{
        static $return=[];
        if(!isset($return[static::class])){
            $return[static::class]=static::getRegionField()!==''&&in_array(static::getRegionField(), static::make()->fields()->column('name'), true);
        }
        return $return[static::class];
    }

    /**
     * 方便不new 获取一些简单数据
     * @return VueCurlModel
     */
    final public static function make(): VueCurlModel
    {
        static $model=[];
        isset($model[static::class])||$model[static::class]=(new static());
        return $model[static::class];
    }
}