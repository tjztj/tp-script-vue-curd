<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class TimeFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class YearFilter extends ModelFilter
{
    protected array $items=[
    ];


    /**
     * @throws \think\Exception
     */
    public function config():array{
        if(empty($this->items)){
            $year=(int)date('Y');
            $this->items=[
                ['start'=>$year,'end'=>$year,'title'=>'今年'],
                ['start'=>$year-1,'end'=>$year-1,'title'=>'去年'],
                ['start'=>$year-2,'end'=>$year-2,'title'=>'前年'],
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
            if(bccomp($value['start'],$value['end'])===-1){
                $query->whereBetween($this->field->name(),[$value['start'],$value['end']]);
            }else{
                $query->whereBetween($this->field->name(),[$value['end'],$value['start']]);
            }
        }
    }

    public static function componentUrl():string{
        return '/tpscriptvuecurd/filter/year.js';
    }

}