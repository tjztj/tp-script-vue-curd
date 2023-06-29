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
    protected array $items=[
        ['start'=>'00:00','end'=>'05:59','title'=>'凌晨'],
        ['start'=>'06:00','end'=>'11:59','title'=>'上午'],
        ['start'=>'12:00','end'=>'18:59','title'=>'下午'],
        ['start'=>'19:00','end'=>'23:59','title'=>'晚上'],
    ];
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
            if(bccomp(strtotime($value['start']),strtotime($value['end']))===-1){
                $query->whereBetween($this->field->name(),[$value['start'],$value['end']]);
            }else{
                $query->whereBetween($this->field->name(),[$value['end'],$value['start']]);
            }
        }
    }

    public static function componentUrl():string{
        return '/tpscriptvuecurd/filter/time.js';
    }

}