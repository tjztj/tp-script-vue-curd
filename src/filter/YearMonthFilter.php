<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class YearMonthFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class YearMonthFilter extends ModelFilter
{
    protected array $items=[];//参考getDefaultItems

    public function config():array{
        if(empty($this->items)){
            $this->items=[
                ['start'=>0,'end'=>11,'title'=>'1年以下'],
                ['start'=>12,'end'=>12*3-1,'title'=>'1年到3（不包含）年'],
                ['start'=>12*3,'end'=>12*5-1,'title'=>'3年到5（不包含）年'],
                ['start'=>12*3,'end'=>12*5-1,'title'=>'5年到10（不包含）年'],
                ['start'=>12*10,'end'=>0,'title'=>'10年及以上'],
            ];
        }
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
            $query->whereBetween($this->field->name(),[$value['start'],$value['end']]);
        }
    }

    public static function componentUrl():string{
        return '/tpscriptvuecurd/filter/year_month.js';
    }
}