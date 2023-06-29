<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class WeekFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class WeekFilter extends ModelFilter
{
    protected array $items=[];//参考getDefaultItems

    public function config():array{
        if(empty($this->items)){
            $this->items=$this->getDefaultItems();
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


    /**
     * 获取默认item配置
     * @return array
     * @throws \think\Exception
     */
    public function getDefaultItems():array{
        $now_w_start_time= self::getWeekStart(time());
        $now_w_start=\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d', $now_w_start_time);

        return [
            ['value'=>$now_w_start,'title'=>'本周'],
            ['value'=>\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d', \tpScriptVueCurd\tool\Time::dateToUnixtime($now_w_start.' -7 day')),'title'=>'上周'],
        ];
    }


    public function generateWhere(Query $query,$value):void{
        if(empty($value)){
            return;
        }
        $query->where($this->field->name(),self::getWeekStart(\tpScriptVueCurd\tool\Time::dateToUnixtime($value)));
    }


    /**
     * 根据一个时间戳获取它的周一
     * @param int $time
     * @return int
     */
    public static function getWeekStart(int $time):int{
        $w=\tpScriptVueCurd\tool\Time::unixtimeToDate('w',$time);
        return $time - (($w?:7) - 1) * 24 * 3600;
    }

    public static function componentUrl():string{
        return '/tpscriptvuecurd/filter/week.js';
    }
}