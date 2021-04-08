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
            $name=$this->field->name();
            if($this->field->canCheckParent()){
                if($this->field->pField()){
                    $name.='|'.$this->field->pField();
                }else if($this->field->cField()){
                    $name.='|'.$this->field->cField();
                }
            }
            foreach ($this->field->getRegionTree() as $v){
                if($v['id']==$value&&!empty($v['children'])){
                    $query->where($name,'in',array_column($v['children'],'id'));
                    return;
                }
            }
            $query->where($name,$value);
        }
    }

    static public function componentUrl():string{
        return '/tp-script-vue-curd-static.php?filter/region.js';
    }
}