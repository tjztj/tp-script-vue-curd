<?php


namespace tpScriptVueCurd\base\controller;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\option\FunControllerChildImportAfter;
use tpScriptVueCurd\option\FunControllerChildImportBefore;
use tpScriptVueCurd\option\FunControllerIndexPage;
use tpScriptVueCurd\option\FunControllerListChildBtn;
use tpScriptVueCurd\traits\controller\CurdChild;
use tpScriptVueCurd\traits\controller\ExcelChild;

/**
 * trait BaseChildController
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\controller
 */
trait BaseChildController
{
    use Controller,CurdChild,ExcelChild{
        ExcelChild::excelSave insteadof Controller;
        ExcelChild::excelFields insteadof Controller;
        Controller::initialize as baseInitialize;
    }
    public VueCurlModel $model;
    public BaseModel $baseModel;
    public FieldCollection $fields;
    public FieldCollection $baseFields;

    public function initialize()
    {
        $this->baseInitialize();
        $model=static::modelClassPath();
        $this->model=new $model;
        $this->fields=$this->model->fields();

        $baseControllerClassPath=static::parentControllerClassPath();
        $baseModel=$baseControllerClassPath::modelClassPath();
        $this->baseModel=new $baseModel;
        $this->baseFields=$this->baseModel->fields();
    }

    /**
     * 控制器类型：base、child、base_have_child
     * @return string
     */
    final public static function type(): string
    {
        return 'child';
    }

    /**父控制器
     * @return string|BaseController
     */
    abstract public static function parentControllerClassPath():string;



    /**
     * 子表在主表列表中的按钮文字，可重写
     * @param BaseModel $info
     * @return FunControllerListChildBtn
     */
    public static function baseListBtnText(BaseModel $info):FunControllerListChildBtn{
        $btn=new FunControllerListChildBtn();
        $btn->color='#d46b08';
        $btn->text='详细列表';
        return $btn;
    }

    /**
     * 列表分页配置
     * @return FunControllerIndexPage
     */
    public static function getIndexPage():FunControllerIndexPage{
        $option=new FunControllerIndexPage;
        $option->pageSize=0;//子表默认不分页
        return $option;
    }

    public function importBefore(FunControllerChildImportBefore $option):void{
        // 数据导入前，方便之类处理（之类重写此方法）
    }

    public function importAfter(FunControllerChildImportAfter $option):void{
        // 数据导入后，方便之类处理（之类重写此方法）
    }

    protected function addBefore(array &$data,BaseModel $baseInfo): void
    {
        // 数据添加钩子，方便之类处理（之类重写此方法）
    }
    protected function editBefore(array &$data,VueCurlModel $old,BaseModel $baseInfo): void
    {
        // 数据添加钩子，方便之类处理（之类重写此方法）
    }

    protected function showBefore(VueCurlModel $info,BaseModel $baseInfo,FieldCollection &$field){
        //数据显示前
    }
}