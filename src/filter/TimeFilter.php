<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class TimeFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class TimeFilter extends ModelFilter
{
    public function config():array{
        return [];
    }

    /**
     * @throws \Think\Exception
     */
    public function generateWhere(Query $query, $value):void{
        if($value){
            $query->where($this->field->name(),$value);
        }
    }

    public static function componentUrl():string{
        return '/tp-script-vue-curd-static.php?filter/time.js';
    }

}