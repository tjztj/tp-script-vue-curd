<?php


namespace tpScriptVueCurd\traits\controller;


use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use think\Request;

/**
 * Trait ExcelChild
 * @property Request $request
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait ExcelChild
{

    public VueCurlModel $model;
    public FieldCollection $fields;
    public BaseModel $baseModel;


    /**
     * 导入关键参数(字段集合)，不需要村社，父表已经有村社了
     * @return FieldCollection
     */
    protected function excelFields():FieldCollection{
        return $this->fields->filter(fn(ModelField $v)=>$v->name()!=='system_region_id'&&$v->name()!=='system_region_pid');
    }


    /**
     * 执行添加逻辑，为父表字段赋值
     * @param $saveData
     * @return mixed
     */
    protected function excelSave($saveData){
        static $modelClassName;
        if(!isset($modelClassName)){
            $modelClassName=get_class($this->model);
        }
        static $baseInfo;
        if(empty($baseInfo)){
            $baseId=$this->request->param('base_id/d');
            if(empty($baseId)){
                throw new \think\Exception('缺少父表参数');
            }
            $baseInfo=$this->baseModel->find($baseId);
            if(empty($baseInfo)){
                throw new \think\Exception('未找到父表相关信息');
            }
        }

        /* @var BaseChildModel $model */
        $model=(new $modelClassName);

        $this->importBefore($saveData,$baseInfo);
        $return=$model->addInfo($saveData,$baseInfo,true);
        $this->importAfter($return,$baseInfo);
        return $return;
    }
}