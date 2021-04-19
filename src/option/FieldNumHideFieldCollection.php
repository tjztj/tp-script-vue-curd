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
     * 筛选获取符合隐藏条件的结果
     * @param $value
     * @return $this
     */
    public function getAccordWithFieds($value):self{
        return $this->checkAccordWithFieds($value,true);
    }

    /**
     * 筛选获取不符合隐藏条件的结果
     * @param $value
     * @return $this
     */
    public function getNotAccordWithFieds($value):self{
        return $this->checkAccordWithFieds($value,false);
    }


    /**
     * 执行筛选条件是否符合
     * @param $value
     * @param bool $isAccordWith
     * @return $this
     */
    private function checkAccordWithFieds($value,bool $isAccordWith):self{
        return $this->filter(function(FieldNumHideField $v)use($value,$isAccordWith){
            return self::checkValueInBetween($value,$v,$isAccordWith);
        });
    }


    public static function checkValueInBetween($value,FieldNumHideField $v,bool $isAccordWith):bool{
        $start=$v->getStart();
        $end=$v->getEnd();
        if($value===''||is_null($value)||(is_null($start)&&is_null($end))){
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
    }

}