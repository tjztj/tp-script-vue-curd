<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\YearMonthFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;


/**
 * 年月###########( 保存的是月数 )
 * Class YearMonthField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class YearMonthField extends ModelField
{
    protected string $defaultFilterClass=YearMonthFilter::class;
    protected $nullVal=0;//字段在数据库中为空时的值


    /**
     * 设置保存的值
     * @param array $data 数据值集合
     * @param BaseModel $old
     * @return $this
     * @throws \think\Exception
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        $name=$this->name();
        if(isset($data[$name])){
            if(is_array($data[$name])){
                $data[$name]=bcadd(bcmul($data[$name][0],12),$data[$name][1]);
            }
            $this->save=$data[$name];
            $this->defaultCheckRequired($this->save);
        }else{
            $this->defaultCheckRequired($this->nullVal());
        }
        return $this;
    }

    /**
     * 显示时要处理的数据
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        if (isset($dataBaseData[$this->name()])) {
            $dataBaseData[$this->name()]=self::monthToStr($dataBaseData[$this->name()]);
        }
    }

    /**
     * 导出到excel时数据处理
     * @param array $data
     * @return string
     */
    public function getExportText(array $data): string
    {
        if (!isset($data[$this->name()])) {
            return '';
        }
        return self::monthToStr($data[$this->name()]);
    }

    /**
     * EXCEL导入时，对数据的处理
     * @param array $save
     * @throws \think\Exception
     */
    public function excelSaveDoData(array &$save):void{
        $name=$this->name();
        if(!isset($save[$name])||$save[$name]===''){
            return;
        }
        $save[$name]=self::strToMonth($save[$name]);
    }


    /**
     * 根据 1年、01年、1年5月、1年5个月、5个月、5月、17个月 获取月数
     * @param string $str
     * @return int
     * @throws \think\Exception
     */
    public static function strToMonth(string $str):int{
        $year_arr=[];
        if(stripos($str,'年')!==false){
            preg_match('/\s*(\d?[1-9]?)\s*年\s*/u',$str,$year_arr);
            if(!isset($year_arr[1])){
                throw new \think\Exception('格式错误');
            }
        }
        $month_arr=[];
        if(stripos($str,'月')!==false){
            preg_match('/\s*(\d?[1-9]?)\s*个?\s*月\s*/u',$str,$month_arr);
            if(!isset($month_arr[1])){
                throw new \think\Exception('格式错误');
            }
        }
        if(isset($year_arr[1])||isset($month_arr[1])){
            return bcadd($month_arr[1]??0,isset($year_arr[1])?bcmul($year_arr[1],12):0);
        }
        throw new \think\Exception('格式错误');
    }


    /**
     * 月份转换为年数，如：17个月转换为1年5个月
     * @param int $months
     * @return string
     */
    public static function monthToStr(int $months):string{
        $year=bcdiv($months,12);
        $month=$months%12;
        if(empty($year)&&empty($month)){
            return '';
        }
        $month=empty($month)?'':$month.'个月';
        $year=empty($year)?'':$year.'年';

        return $year.$month;
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->explain="请填入几年几个月，如：\n1年、01年、1年5月、1年5个月、5个月、5月、17个月";
        $excelFieldTpl->width=40;
        $excelFieldTpl->wrapText=true;
        $excelFieldTpl->isText=true;
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tpscriptvuecurd/field/year_month/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeInt();
    }
}