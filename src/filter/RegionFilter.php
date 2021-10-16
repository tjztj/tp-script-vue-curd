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
            $ids=[$value];
            $getChildIds=function ($tree,$isChecked)use($value,&$ids,&$getChildIds){
                foreach ($tree as $v){
                    if($isChecked){
                        $ids[]=$v['value'];
                        if(!empty($v['children'])){
                            $getChildIds($v['children'],true);
                        }
                    }else{
                        if($v['value']===$value){
                            empty($v['children'])||$getChildIds($v['children'],true);
                            break;
                        }else{
                            $getChildIds($v['children'],false);
                        }
                    }
                }
            };
            $getChildIds($this->field->getRegionTree(),false);


            if(count($ids)>100){
                $query->where(function (Query $q)use($value){
                    $q->whereRaw($this->field->name().' IN '.\app\admin\model\SystemRegion::whereFindInSet('pids',$value)
                            ->field('id')->buildSql())
                        ->whereOr($this->field->name(),$value);
                });
            }else{
                $name=$this->field->name();
                if($this->field->pField()){
                    $name.='|'.$this->field->pField();
                }else if($this->field->cField()){
                    $name.='|'.$this->field->cField();
                }else{
                    $query->where($name,'in',$ids);
                    return;
                }
                $query->where(function (Query $q)use($ids,$name,$value){
                    $q->whereOr($name,$value)->whereOr($this->field->name(),'in',$ids);
                });
            }

        }
    }

    static public function componentUrl():string{
        return '/tp-script-vue-curd-static.php?filter/region.js';
    }
}