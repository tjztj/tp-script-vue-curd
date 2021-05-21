<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\DateFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\NumHideFields;

/**
 * 日期
 * Class DateField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\field
 */
class DateField extends ModelField
{
    use NumHideFields;

    protected int $listColumnWidth=108;//设置默认值
    protected string $defaultFilterClass=DateFilter::class;
    protected $nullVal=0;//字段在数据库中为空时的值
    protected ?int $max=null;
    protected ?int $min=null;

    public function __construct()
    {
        parent::__construct();
        $this->max=time();
    }


    /**最小值
     * @param int|null $min
     * @return $this|int
     */
    public function min(int $min = null)
    {
        return $this->doAttr('min', $min);
    }

    /**最大值
     * @param int|null $max
     * @return $this|int
     */
    public function max(int $max = null)
    {
        return $this->doAttr('max', $max);
    }

    public function minNull(){
        $this->min=null;
        return $this;
    }

    public function maxNull(){
        $this->max=null;
        return $this;
    }


    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        $name=$this->name();
        if(isset($data[$name])){
            if($data[$name]){
                $data[$name]=trim(str_replace(['.','年','月','日','/'],'-',$data[$name]),'-');//让点也能当作日期符号
                $this->save=is_numeric($data[$name])?$data[$name]:\tpScriptVueCurd\tool\Time::dateToUnixtime($data[$name]);
                if($this->save===false){
                    throw new \think\Exception('不是正确的日期格式');
                }
                if($this->save!==0){
                    if($this->min!==null&&$this->min>$this->save){
                        throw new \think\Exception('日期不能小于'.date('Y-m-d',$this->min));
                    }
                    if($this->max!==null&&$this->max<$this->save){
                        throw new \think\Exception('日期不能大于'.date('Y-m-d',$this->max));
                    }
                }
            }else{
                $this->save=$this->nullVal();
                $this->defaultCheckRequired($this->nullVal(),'请选择日期');
            }
        }else{
            $this->defaultCheckRequired($this->nullVal(),'请选择日期');
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
        if(isset($dataBaseData[$name])){
            if(empty($dataBaseData[$name])){
                $dataBaseData[$name]='';
            }else if(is_numeric($dataBaseData[$name])){
                //有些时间戳小于10位
                $dataBaseData[$name]=\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d',$dataBaseData[$name]);
            }
        }
    }


    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->explain="请填入日期，如：\n2021/01/01\n2020/1/1\n2020-01-01\n2020.01.01\n2020年01月01日";
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
            new Edit($type,'/tp-script-vue-curd-static.php?field/date/edit.js')
        );
    }

}