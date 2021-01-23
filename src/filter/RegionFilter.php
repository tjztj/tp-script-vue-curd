<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class RegionFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class RegionFilter extends ModelFilter
{
    public function config():array{
        return [
            'regionTree'=>$this->field->getRegionTree(),
        ];
    }
    public function generateWhere(Query $query,$value):void{
        if($value){
            foreach ($this->field->getRegionTree() as $v){
                if($v['id']==$value&&!empty($v['children'])){
                    $query->where($this->field->name(),'in',array_column($v['children'],'id'));
                    return;
                }
            }
            $query->where($this->field->name(),$value);
        }
    }
}