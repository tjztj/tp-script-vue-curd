<?php


namespace tpScriptVueCurd\base\controller;


use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\option\FunControllerImportAfter;
use tpScriptVueCurd\option\FunControllerImportBefore;
use tpScriptVueCurd\option\FunControllerIndexPage;
use tpScriptVueCurd\traits\controller\Curd;


/**
 * Trait BaseController
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\controller
 */
trait BaseController
{
    use Controller,Curd{
        Controller::initialize as baseInitialize;
    }
    public FieldCollection $fields;


    public function initialize()
    {
        $this->baseInitialize();
        $model=static::modelClassPath();
        $this->model=new $model;
        $this->fields=$this->model->fields();

        $indexPageOption=new FunControllerIndexPage;
        $indexPageOption->pageSize=10;//默认每页显示10条数据
        static::getIndexPage($indexPageOption);
        $this->indexPageOption=$indexPageOption;
    }

    /**
     * 控制器类型：base、child、base_have_child
     * @return string
     */
    final public static function type(): string
    {
        return 'base';
    }

    public static function getIndexPage(FunControllerIndexPage $indexPageOption):void{
    }

    public function importBefore(FunControllerImportBefore $option):void{
        // 数据导入前，方便之类处理（之类重写此方法）
    }

    public function importAfter(FunControllerImportAfter $option):void{
        // 数据导入后，方便之类处理（之类重写此方法）
    }

    protected function addBefore(array &$data): void
    {
        // 数据添加钩子，方便之类处理（之类重写此方法）
    }
    protected function editBefore(array &$data,VueCurlModel $old): void
    {
        // 数据添加钩子，方便之类处理（之类重写此方法）
    }

    protected function showBefore(VueCurlModel $info,FieldCollection &$field){
        //数据显示前
    }
}