<?php

namespace tpScriptVueCurd\traits\controller;

use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;

trait BaseShow
{
    /**
     * #title 详细页面
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function show(){
        $id=$this->request->showId??$this->request->param('id/d');
        if(empty($id)){
            return $this->errorAndCode('缺少必要参数');
        }
        $data=(clone $this->model)->find($id);
        if(empty($data)){
            return $this->errorAndCode('未找到相关数据信息');
        }


        $parentInfo=$this->parentController?(clone $this->parentController->model)::find($data[$this->model::parentField()]):null;

        try{
            //字段钩子
            FieldDo::doShowBefore($this->fields,$data,$parentInfo);
        }catch (\Exception $e){
            return $this->error($e);
        }


        $fields=$this->fields->filterHideFieldsByShow($data)->whenShowSetAttrValByWheres($data)->filterShowStepFields($data,$parentInfo)->rendGroup();


        try{
            $canShow=$data->checkRowAuth($fields,$parentInfo,'show');
        }catch (\Exception $exception){
            return $this->errorAndCode($exception->getMessage());
        }
        if($canShow===false){
            return $this->errorAndCode('您不能查看当前数据信息');
        }


        try{
            $fields->each(function (ModelField $v)use($data,$parentInfo){$v->onShow($data,$parentInfo);});
            //字段钩子
            FieldDo::doShow($fields,$data,$parentInfo);
        }catch (\Exception $e){
            return $this->error($e);
        }


        //控制器钩子
        $this->showBefore($data,$parentInfo,$fields);


        $info=$data->toArray();
        return $this->doShow($this->title,$info,$fields);
    }

    /**
     * 方便可以调用其他模型的查看页面（项目开发中可能会用到）
     * @param string $title
     * @param array $info
     * @param FieldCollection $fields
     * @return mixed
     */
    protected function doShow(string $title,array $info,FieldCollection $fields){
        $this->assign('thisAction','show');//使用它的js

        $fields=$fields->filter(fn(ModelField $v)=>$v->showPage())->rendGroup();


        $fields->doShowData($info);
        $fieldArr=array_values($fields->toArray());

        return $this->showTpl('show',$this->showFetch([
            'title'=>$title,
            'fields'=>$fieldArr,
            'groupFields'=>$fields->groupItems?FieldCollection::groupListByItems($fieldArr):null,
            'info'=>$info,
            'fieldComponents'=>$fields->getComponents('show'),
        ]));
    }
}