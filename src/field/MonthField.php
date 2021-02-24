<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\MonthFilter;
use tpScriptVueCurd\ModelField;


/**
 * 月份选择
 * Class MonthField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\field
 */
class MonthField extends ModelField
{

    protected int $listColumnWidth = 110;//设置默认值
    protected string $defaultFilterClass = MonthFilter::class;


    /**
     * 设置保存的值
     * @param array $data 数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        $name=$this->name();
        if (isset($data[$name])) {
            if($data[$name]){
                $data[$name]=trim(str_replace(['.','年','月','日','/'],'-',$data[$name]),'-');//让点也能当作日期符号
                $this->save=\tpScriptVueCurd\tool\Time::dateToUnixtime($data[$name].'-01');
                if($this->save===false){
                    throw new \think\Exception('不是正确的月份格式');
                }
            }else{
                $this->save=0;
                $this->defaultCheckRequired(0,'请选择月份');
            }
        }else{
            $this->defaultCheckRequired('');
        }
        return $this;
    }

    /**
     * 显示时要处理的数据
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        $name=$this->name();
        if (isset($dataBaseData[$name])) {
            $dataBaseData[$name] = empty($dataBaseData[$name]) ? '' : \tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m',$dataBaseData[$name]);
        }
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->explain="请填入年月，如：\n2021/01\n2020/1\n2020-01\n2020.01\n2020年01月";
        $excelFieldTpl->width=20;
        $excelFieldTpl->wrapText=true;
        $excelFieldTpl->isText=true;
    }

}