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
     * @return array
     * @throws \think\Exception
     */
    public function getFilterConfigDateStr(string $dateType, bool $accurateTime = false): array
    {
        $now_m = \tpScriptVueCurd\tool\Time::unixtimeToDate('m');
        $now_y = \tpScriptVueCurd\tool\Time::unixtimeToDate('Y');
        $now_d = \tpScriptVueCurd\tool\Time::unixtimeToDate('d');
        $end_s = $accurateTime ? ' 23:59:59' : '';
        switch ($dateType) {
            case '本周':
                //本周
                $begin_time = mktime(0, 0, 0, $now_m, $now_d - \tpScriptVueCurd\tool\Time::unixtimeToDate('w') + 1, $now_y);
                $end_time = mktime($accurateTime ? 23 : 0, $accurateTime ? 59 : 0, $accurateTime ? 59 : 0, $now_m, $now_d - \tpScriptVueCurd\tool\Time::unixtimeToDate('w') + 7, $now_y);
                break;
            case '本月':
                //本月
                $begin_time = mktime(0, 0, 0, $now_m, 1, $now_y);
                $end_time = mktime($accurateTime ? 23 : 0, $accurateTime ? 59 : 0, $accurateTime ? 59 : 0, \tpScriptVueCurd\tool\Time::unixtimeToDate("m"), \tpScriptVueCurd\tool\Time::unixtimeToDate("t"), $now_y);
                break;
            case '上月':
                //上月
                $begin_time = \tpScriptVueCurd\tool\Time::dateToUnixtime(\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-01', \tpScriptVueCurd\tool\Time::dateToUnixtime('-1 month')));
                $end_time = \tpScriptVueCurd\tool\Time::dateToUnixtime(\tpScriptVueCurd\tool\Time::unixtimeToDate("Y-m-d" . $end_s, \tpScriptVueCurd\tool\Time::dateToUnixtime(-$now_d . 'day')));
                break;
            case '今年':
                //今年
                $begin_time = \tpScriptVueCurd\tool\Time::dateToUnixtime($now_y . "-1" . "-1");
                $end_time = \tpScriptVueCurd\tool\Time::dateToUnixtime($now_y . "-12" . "-31" . $end_s);
                break;
            case '去年':
                //去年
                $begin_time = \tpScriptVueCurd\tool\Time::dateToUnixtime(($now_y - 1) . "-1" . "-1");
                $end_time = \tpScriptVueCurd\tool\Time::dateToUnixtime(($now_y - 1) . "-12" . "-31" . $end_s);
                break;
            default:
                throw new \think\Exception('getFilterConfigDateStr传入参数错误');
        }
        return ['start'=>\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d', $begin_time) ,'end'=>\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d', $end_time).$end_s,'title'=>$dateType];
    }


    public function generateWhere(Query $query,$value):void{
        if(empty($value)||!is_array($value)||(empty($value['start'])&&empty($value['end']))){
            return;
        }
        if(empty($value['end'])){
            $query->where($this->field->name(),'>=',\tpScriptVueCurd\tool\Time::dateToUnixtime($value['start']));
        }else if(empty($value['start'])){
            $query->where($this->field->name(),'<=',\tpScriptVueCurd\tool\Time::dateToUnixtime($value['end'].' 23:59:59'));
        }else{
            $query->whereBetween($this->field->name(),[\tpScriptVueCurd\tool\Time::dateToUnixtime($value['start']),\tpScriptVueCurd\tool\Time::dateToUnixtime($value['end'].' 23:59:59')]);
        }
    }


    public static function componentUrl():string{
        return '/tpscriptvuecurd/filter/date.js';
    }
}