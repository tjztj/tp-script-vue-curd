<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class BetweenFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class BetweenFilter extends ModelFilter
{
    protected array $items=[];
    //如：
    //[
    //  ['start'=>0,'end'=>3000,'title'=>'叁仟元及以下'],
    //  ['start'=>3000,'end'=>10000,'title'=>'叁仟到壹万元'],
    //  ['start'=>10000,'end'=>0,'title'=>'壹万元以上'],
    //]


    /**
     * @throws \think\Exception
     */
    protected function config():array{
//        if(empty($this->items)){
//            throw new \think\Exception('字段[ '.$this->field->name().' ]未设置【筛选】默认选项');
//        }
        return [
            'items'=>$this->items,
        ];
    }

    /**
     * 设置筛选选项
     * @param array $items
     * @return $this
     */
    public function setItems(array $items): self
    {
        $this->items=$items;
        return $this;
    }

    public function generateWhere(Query $query,$value):void{
        if(empty($value)||!is_array($value)||(empty($value['start'])&&empty($value['end']))){
            return;
        }
        if(empty($value['end'])){
            $query->where($this->field->name(),'>=',$value['start']);
        }else if(empty($value['start'])){
            $query->where($this->field->name(),'<=',$value['end']);
        }else{
            if(bccomp($value['start'],$value['end'])===-1){
                $query->whereBetween($this->field->name(),[$value['start'],$value['end']]);
            }else{
                $query->whereBetween($this->field->name(),[$value['end'],$value['start']]);
            }
        }
    }

    public static function componentUrl():string{
        return '/tpscriptvuecurd/filter/between.js';
    }

}