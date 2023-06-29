<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class ValueFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class ValueFilter extends ModelFilter
{
    public function config():array{
        return [];
    }

    public function generateWhere(Query $query,$value):void{
        if($value||$value===0||$value==='0'){
            if(is_string($value)){
                $query->where($this->field->name(),trim($value));
            }else{
                if(empty($value[0])||($value[0]!=='='&&strtolower($value[0])!=='eq'&&strtolower($value[0])!=='like')||!isset($value[1])||!is_string($value[1])){
                    throw new \Think\Exception('查询参数错误');
                }
                $value[1]=trim($value[1]);
                if(strtolower($value[0])==='like'){
                    $query->where($this->field->name(),'like',"%{$value[1]}%");
                }else{
                    $query->where($this->field->name(),$value[1]);
                }
            }
        }
    }


    public static function componentUrl():string{
        return '/tpscriptvuecurd/filter/value.js';
    }
}