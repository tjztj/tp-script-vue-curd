<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class MonthFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class MonthFilter extends ModelFilter
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
        $now_m_start=date('Y-m');
        $last_m_start= date('Y-m',strtotime($now_m_start.' -1 month'));

        return [
            ['start'=>$now_m_start,'end'=>$now_m_start,'title'=>'本月'],
            ['start'=>$last_m_start,'end'=>$last_m_start,'title'=>'上月'],
            ['start'=>date('Y-01'),'end'=>date('Y-12'),'title'=>'今年'],
        ];
    }


    public function generateWhere(Query $query,$value):void{
        if(empty($value)){
            return;
        }

        empty($value['start'])||$query->where($this->field->name(),'>=',strtotime($value['start'].'-01'));
        empty($value['end'])||$query->where($this->field->name(),'<=',strtotime($value['end'].'-01 +1 month')-1);
    }


}