<?php

namespace tpScriptVueCurd\traits\controller;

use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;

/**
 * @property BaseModel $md
 */
trait BaseShow
{
    /**
     * #title 详细页面
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function show(){
        $id=$this->request->showId??$this->request->param('id/d');
        if(empty($id)){
            return $this->errorAndCode('缺少必要参数');
        }
        $data=(clone $this->md)->find($id);
        if(empty($data)){
            return $this->errorAndCode('未找到相关数据信息');
        }


        $parentInfo=$this->getParentController()?(clone $this->getParentController()->md)::find($data[$this->md::parentField()]):null;

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
            return $this->errorAndCode($exception);
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
        return $this->doShow($this->title,$info,$fields,$data,$parentInfo);
    }

    /**
     * 方便可以调用其他模型的查看页面（项目开发中可能会用到）
     * @param string $title
     * @param array $info
     * @param FieldCollection $fields
     * @return mixed
     */
    protected function doShow(string $title,array $info,FieldCollection $fields,BaseModel $data,?BaseModel $baseModel){
        $this->assign('thisAction','show');//使用它的js

        $fields=$fields->filter(fn(ModelField $v)=>$v->showPage())->rendGroup();


        $fields->doShowData($info);
        $fieldArr=array_values($fields->fieldToArrayPageType('show')->toArray());



        $groupFields=$fields->groupItems?FieldCollection::groupListByItems($fieldArr):null;


        $fields->each(function (ModelField $field)use($data,$baseModel){
            $func=$field->getShowGridBy();
            $func&&$field->grid($func($data,$baseModel,$field));
        });
        $groupGrids=[];
        foreach ($groupFields?:[''=>$fields->all()] as $k=>$v){
            $func=$fields->getShowGridBy();
            $groupGrids[$k]=$func?$func($data,$baseModel,$v,$k):null;
        }
        FieldDo::doShowAfter($fields,$info,$baseModel);

        return $this->showTpl('show',$this->showFetch([
            'title'=>$title,
            'fields'=>$fieldArr,
            'groupFields'=>$groupFields,
            'groupGrids'=>$groupGrids,
            'info'=>$info,
            'fieldComponents'=>$fields->getComponents('show'),
        ]));
    }
}