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
            $query->where(function (Query $q)use($value){
                $q->whereRaw($this->field->name().' IN '.\app\admin\model\SystemRegion::whereFindInSet('pids',$value)
                        ->field('id')->buildSql())
                    ->whereOr($this->field->name(),$value);
            });
        }
    }

    public static function componentUrl():string{
        return '/tp-script-vue-curd-static.php?filter/region.js';
    }
}