<?php


namespace tpScriptVueCurd\base\controller;


use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\traits\controller\ExcelHaveChild;
use think\App;

/**
 * trait BaseHaveChildController
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\base\controller
 */

trait BaseHaveChildController
{
    use BaseController,ExcelHaveChild{
        ExcelHaveChild::excelSave insteadof  BaseController;
        ExcelHaveChild::excelFields insteadof  BaseController;
        ExcelHaveChild::importExcelTpl insteadof  BaseController;
        ExcelHaveChild::downExcelTpl insteadof  BaseController;
        ExcelHaveChild::getExcelBaseInfo insteadof  BaseController;
        ExcelHaveChild::getExcelBaseInfos insteadof  BaseController;
        ExcelHaveChild::getMainIdByImportData insteadof  BaseController;
        ExcelHaveChild::justDownBaseExcelTpl insteadof  BaseController;
        ExcelHaveChild::setExcelBaseInfo insteadof  BaseController;
        ExcelHaveChild::excelTilte insteadof  BaseController;
        BaseController::indexFetch as baseIndexFetch;
    }


    /**
     * 控制器类型：base、child、base_have_child
     * @return string
     */
    final public static function type(): string
    {
        return 'base_have_child';
    }

    /**
     * 获取子控制器，也用于判断是否有子控制器，
     * @return BaseChildController[]
     */
    abstract public static function childControllerClassPathList():array;


    /**
     * 获取子表模型对象实例
     * @return BaseChildModel[]
     */
    public static function childModelObjs():array{
        static $models=[];
        if(empty($models)){
            //子表
            foreach (static::childControllerClassPathList() as $childControllerClass){
                /**
                 * @var BaseChildController|string $childControllerClass
                 */
                $modelClass=$childControllerClass::modelClassPath();
                $models[$childControllerClass]=new $modelClass();
            }
        }
        return $models;
    }

    /**
     * 获取子表模型对象字段实例
     * @return FieldCollection[]
     */
    public static function childFieldObjs():array{
        static $field=[];
        if(empty($field)){
            //子表
            foreach (static::childModelObjs() as $childControllerClass=>$model){
                $field[$childControllerClass]=$model->fields();
            }
        }
        return $field;
    }


    /**
     * index页面显示的数据处理
     * @param array $fetch
     * @return array
     */
    protected function indexFetch(array $fetch):array{
        // 列表页面显示前处理
        $fetch=$this->baseIndexFetch($fetch);

        $fetch['childs']=[];
        foreach (static::childControllerClassPathList() as $childControllerClass){
            /* @var $childControllerClass BaseChildController|string */
            /* @var $childModel BaseChildModel */
            $childModelClass=$childControllerClass::modelClassPath();
            $childModel=new $childModelClass;
            $name=class_basename($childModelClass);
            $fetch['childs'][]=[
                'class'=>$childModelClass,
                'name'=>$name,
                'listBtn'=>$childControllerClass::baseListBtnText(),
                'filterData'=>json_decode($this->request->param($name.'filterData','',null)),
                'title'=>$childModel::getTitle(),
                'filterConfig'=>$childModel->fields()->filter(fn(ModelField $v)=>$v->name()!==$childModel::getRegionField()&&$v->name()!==$childModel::getRegionPidField())->getFilterShowData(),
                'listUrl'=>url(str_replace('\\','.',parse_name(ltrim(str_replace($this->app->getNamespace().'\\controller\\','',$childControllerClass),'\\'))).'/index')->build(),
            ];
        }
        return $fetch;
    }
}