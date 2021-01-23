<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use think\db\Query;

/**
 * Class DateFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class DateFilter extends ModelFilter
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

        return [
            $this->getFilterConfigDateStr('本周',true),
            $this->getFilterConfigDateStr('本月',true),
            $this->getFilterConfigDateStr('上月',true),
            $this->getFilterConfigDateStr('今年',true),
            $this->getFilterConfigDateStr('去年',true),
        ];
    }



    /**获取时间筛选 配置 的key
     * @param string $dateType
     * @param bool $accurateTime 结束时间精确到秒
     * @return string
     */
    public function getFilterConfigDateStr(string $dateType, $accurateTime = false)
    {
        $now_m = date('m');
        $now_y = date('Y');
        $now_d = date('d');
        $end_s = $accurateTime ? ' 23:59:59' : '';
        switch ($dateType) {
            case '本周':
                //本周
                $begin_time = mktime(0, 0, 0, $now_m, $now_d - date('w') + 1, $now_y);
                $end_time = mktime($accurateTime ? 23 : 0, $accurateTime ? 59 : 0, $accurateTime ? 59 : 0, $now_m, $now_d - date('w') + 7, $now_y);
                break;
            case '本月':
                //本月
                $begin_time = mktime(0, 0, 0, $now_m, 1, $now_y);
                $end_time = mktime($accurateTime ? 23 : 0, $accurateTime ? 59 : 0, $accurateTime ? 59 : 0, date("m"), date("t"), $now_y);
                break;
            case '上月':
                //上月
                $begin_time = strtotime(date('Y-m-01', strtotime('-1 month')));
                $end_time = strtotime(date("Y-m-d" . $end_s, strtotime(-$now_d . 'day')));
                break;
            case '今年':
                //今年
                $begin_time = strtotime($now_y . "-1" . "-1");
                $end_time = strtotime($now_y . "-12" . "-31" . $end_s);
                break;
            case '去年':
                //去年
                $begin_time = strtotime(($now_y - 1) . "-1" . "-1");
                $end_time = strtotime(($now_y - 1) . "-12" . "-31" . $end_s);
                break;
            default:
                throw new \think\Exception('getFilterConfigDateStr传入参数错误');
        }
        return ['start'=>date('Y-m-d', $begin_time) ,'end'=>date('Y-m-d', $end_time),'title'=>$dateType];
    }


    public function generateWhere(Query $query,$value):void{
        if(empty($value)||!is_array($value)||(empty($value['start'])&&empty($value['end']))){
            return;
        }
        if(empty($value['end'])){
            $query->where($this->field->name(),'>=',strtotime($value['start']));
        }else if(empty($value['start'])){
            $query->where($this->field->name(),'<=',strtotime($value['end'].' 23:59:59'));
        }else{
            $query->whereBetween($this->field->name(),[strtotime($value['start']),strtotime($value['end'].' 23:59:59')]);
        }
    }
}