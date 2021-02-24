<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\DateFilter;
use tpScriptVueCurd\filter\WeekFilter;
use tpScriptVueCurd\ModelField;


/**
 * 周选择
 * Class WeekField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\field
 */
class WeekField extends ModelField
{

    protected int $listColumnWidth = 330;//设置默认值
    protected string $defaultFilterClass = WeekFilter::class;


    /**
     * 设置保存的值
     * @param array $data 数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        $name=$this->name();
        if (isset($data[$name])) {
            $data[$name]=trim($data[$name]);
            if(empty($data[$name])){
                $this->defaultCheckRequired(0,'请选择日期');
                $this->save =0;
            }else{
                $data[$name]=trim(str_replace(['.','年','月','日','/'],'-',$data[$name]),'-');//让点也能当作日期符号
                $time=\tpScriptVueCurd\tool\Time::dateToUnixtime($data[$name]);
                if($time===false){
                    throw new \think\Exception('周时间格式不正确');
                }
                //获取周一日期
                $w=\tpScriptVueCurd\tool\Time::unixtimeToDate('w',$time);
                $this->save=\tpScriptVueCurd\tool\Time::dateToUnixtime(\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d',$time - (($w?:7) - 1) * 24 * 3600));
            }
        }
        return $this;
    }

    /**
     * 显示时要处理的数据
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        if (isset($dataBaseData[$this->name])) {
            $dataBaseData[$this->name] = empty($dataBaseData[$this->name]) ? '' : self::getWeekStr($dataBaseData[$this->name]);
        }
    }


    /**
     * 根据时间获取月第几周
     * @param int $time
     * @param bool $showDate
     * @return string
     */
    public static function getWeekStr(int $time,bool $showDate=true):string{
        $day=\tpScriptVueCurd\tool\Time::unixtimeToDate('d',$time); //今天几号
        //本月1号是星期几
        $begin_w=(int)\tpScriptVueCurd\tool\Time::unixtimeToDate('w',\tpScriptVueCurd\tool\Time::dateToUnixtime(\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-01',$time)));
        $begin_w||$begin_w=7;
        if($begin_w!==1){
            //如果不是星期一
            //获取上一个月剩余在这里的天数
            $last_day_num=bcadd(bcsub(7,$begin_w),1);
            if($last_day_num>=$day){
                //那么这是上一月的
                return self::getWeekStr(\tpScriptVueCurd\tool\Time::dateToUnixtime(\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-01',$time))-1);
            }else{
                $num=ceil(bcdiv(bcsub($day,$last_day_num),7,1)); //向上取整
                $month=\tpScriptVueCurd\tool\Time::unixtimeToDate('Y年m月',$time);
            }
        }else{
            $num=ceil(bcdiv($day,7,1)); //向上取整
            $month=\tpScriptVueCurd\tool\Time::unixtimeToDate('Y年m月',$time);
        }

        $start=WeekFilter::getWeekStart($time);
        $end=\tpScriptVueCurd\tool\Time::dateToUnixtime(\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d',$start).' +6 day');//+7是下周星期一，我要的是本周星期日，所以加6
        $start=\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d',$start);
        $end=\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d',$end);
        $return="{$month}第{$num}周";
        if($showDate){
            $return.="（{$start} ~ {$end}）";
        }
        return $return;
    }


    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->explain="填写日期即可，将自动判断属于那一周，如：\n2021/01/01\n2020/1/1\n2020-01-01\n2020.01.01\n2020年01月01日";
        $excelFieldTpl->width=40;
        $excelFieldTpl->wrapText=true;
        $excelFieldTpl->isText=true;
    }
}