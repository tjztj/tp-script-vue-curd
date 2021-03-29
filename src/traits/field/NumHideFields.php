<?php


namespace tpScriptVueCurd\traits\field;


use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\option\FieldNumHideField;
use tpScriptVueCurd\option\FieldNumHideFieldCollection;

trait NumHideFields
{
    use HideFields;

    protected FieldNumHideFieldCollection $hideFields;

    /**
     * 字段的值在什么范围时隐藏某值（传入的时间范围是时间戳）
     * @param null $hideFields
     * @return $this|FieldNumHideFieldCollection
     */
    public function hideFields($hideFields=null){
        if(is_null($hideFields)){
            return $this->hideFields??null;
        }

        if(is_array($hideFields)){
            /*
             * start 和 end 可以等于null，代表无限小和无限大
             * [
             *      [start<int|float> , end<int|float> , fields<FieldCollection>],//或者
             *      [start<int|float> , end<int|float> , fields<[]>],//FieldCollection[]   或者
             *      item<FieldNumHideField>
             * ]
             */
            $configs=FieldNumHideFieldCollection::make([]);
            foreach ($hideFields as $v){
                if(is_array($v)){
                    $configs->push(FieldNumHideField::init($v[0],$v[1],is_array($v[2])?FieldCollection::make($v[2]):$v[2]));
                }else{
                    $configs->push($v);
                }
            }
            $this->hideFields=$configs;
        }else{
            $this->hideFields=$hideFields;
        }
        return $this;
    }

    /**
     * 默认隐藏相关字段
     * @param bool|null $defHideAboutFields
     * @return bool|$this
     */
    public function defHideAboutFields(bool $defHideAboutFields=null){
        return $this->doAttr('defHideAboutFields',$defHideAboutFields);
    }
}