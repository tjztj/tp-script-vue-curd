<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;


/**
 * 空筛选，不做操作
 * Class EmptyFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class EmptyFilter extends ModelFilter
{

    public function config():array{
        return [];
    }
    public function generateWhere(Query $query,$value):void{}

    public static function componentUrl():string{
        return '';
    }
}