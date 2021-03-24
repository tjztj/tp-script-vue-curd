<?php


namespace tpScriptVueCurd\option;


use think\Collection;

class FieldNumHideFieldCollection extends Collection
{

    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value->toArray();
        }, $this->items);
    }


    /**
     * 筛选获取符合条件的结果
     * @param $value
     * @return $this
     */
    public function getAccordWithFieds($value):self{
        $this->checkAccordWithFieds($value,true);
    }

    /**
     * 筛选获取不符合条件的结果
     * @param $value
     * @return $this
     */
    public function getNotAccordWithFieds($value):self{
        $this->checkAccordWithFieds($value,false);
    }


    /**
     * 执行筛选条件是否符合
     * @param $value
     * @param bool $isAccordWith
     * @return $this
     */
    private function checkAccordWithFieds($value,bool $isAccordWith):self{
        return $this->filter(function(FieldNumHideField $v)use($value,$isAccordWith){
            $start=$v->getStart();
            $end=$v->getEnd();
            if(is_null($start)&&is_null($end)){
                return $isAccordWith;
            }
            if(is_null($start)){
                //无限小
                return $value<=$end?$isAccordWith:!$isAccordWith;
            }
            if(is_null($end)){
                //无限大
                return $value>=$start?$isAccordWith:!$isAccordWith;
            }
            return $value>=$start&&$value<=$end?$isAccordWith:!$isAccordWith;
        });
    }

}