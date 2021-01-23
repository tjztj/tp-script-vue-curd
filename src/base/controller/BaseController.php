<?php


namespace tpScriptVueCurd\base\controller;


use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\traits\controller\Curd;
use think\App;


/**
 * Trait BaseController
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\controller
 */
trait BaseController
{
    use Controller,Curd;
    public FieldCollection $fields;


    public function __construct(App $app)
    {
        parent::__construct($app);
        $model=static::modelClassPath();
        $this->model=new $model;
        $this->fields=$this->model->fields();
    }

    /**
     * 控制器类型：base、child、base_have_child
     * @return string
     */
    public static function type(): string
    {
        return 'base';
    }


    public function importBefore(array $saveObjects):void{
        // 数据导入前，方便之类处理（之类重写此方法）
    }

    public function importAfter(VueCurlModel $saveObjects):void{
        // 数据导入后，方便之类处理（之类重写此方法）
    }
}