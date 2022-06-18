<?php


namespace tpScriptVueCurd\base\model;



use think\Model;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\traits\model\GenerateTable;
use tpScriptVueCurd\traits\model\InfoAuth;
use tpScriptVueCurd\traits\model\ModelBaseField;
use tpScriptVueCurd\traits\model\ModelDelTraits;
use tpScriptVueCurd\traits\model\ModelRowBtn;
use tpScriptVueCurd\traits\model\ModelSave;
use tpScriptVueCurd\traits\model\ModelStep;

/**
 * Class VueCurlModel
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\model
 */
abstract class VueCurlModel extends TimeModel
{
    use ModelSave,ModelDelTraits,ModelBaseField,ModelStep,InfoAuth,GenerateTable,ModelRowBtn;

    /**
     * @var Controller
     */
    public $controller;

    public function __construct(array $data=[],$controller=null)
    {
        parent::__construct($data);
        $this->controller=$controller;
        $this->doGenerateTable();
    }

    public function newInstance(array $data = [], $where = null, array $options = []): Model
    {
        $md=parent::newInstance($data, $where,$options);
        if($this->controller){
            $md->controller=$this->controller;
        }
        return $md;
    }

    abstract public function fields():FieldCollection;




    /**
     * 方便不new 获取一些简单数据
     * @param Controller $controller
     * @return VueCurlModel
     */
    public static function make($controller): VueCurlModel
    {
        static $model=[];
        isset($model[static::class])||$model[static::class]=(new static([],$controller));
        return $model[static::class];
    }
}