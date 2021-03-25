<?php


namespace tpScriptVueCurd\option;


use think\Collection;
use tpScriptVueCurd\base\model\VueCurlModel;

class FieldStepCollection extends Collection
{

    public function check(VueCurlModel $old,array $new,bool $checkFieldFunc=false):bool{
        $list=$this->filter(fn(FieldStep $v)=>$v->check($old,$new,$checkFieldFunc));
        if($list->count()>1){
            throw new \think\Exception('同时满足多个步骤是错误的 '.implode('、',$list->column('name')));
        }

        return $list->count()===1;
    }

    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value->toArray();
        }, $this->items);
    }
}