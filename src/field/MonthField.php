<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\MonthFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\NumHideFields;


/**
 * 月份选择
 * Class MonthField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\field
 */
class MonthField extends ModelField
{
    use NumHideFields;
    protected int $listColumnWidth = 110;//设置默认值
    protected string $defaultFilterClass = MonthFilter::class;
    protected $nullVal=0;//字段在数据库中为空时的值


    protected ?int $max=null;
    protected ?int $min=null;


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

    public function minNull(): self
    {
        $this->min=null;
        $this->fieldPushAttrByWhere('min',$this->min);
        return $this;
    }

    public function maxNull(): self
    {
        $this->max=null;
        $this->fieldPushAttrByWhere('max',$this->max);
        return $this;
    }

    /**
     * 设置保存的值
     * @param array $data 数据值集合
     * @return $this
     * @throws \think\Exception
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        $name=$this->name();
        if (isset($data[$name])) {
            if($data[$name]){
                $data[$name]=is_numeric($data[$name])?$data[$name]:trim(str_replace(['.','年','月','日','/'],'-',$data[$name]),'-');//让点也能当作日期符号
                if(is_numeric($data[$name])){
                    $this->save=$data[$name];
                }else{
                    $timeArr=array_filter(explode('-',$data[$name]));
                    $this->save=\tpScriptVueCurd\tool\Time::dateToUnixtime($timeArr[0].'-'.$timeArr[1].'-01');
                    if($this->save===false){
                        throw new \think\Exception('不是正确的月份格式');
                    }
                }
                if($this->save!==0){
                    if($this->min!==null&&$this->min>$this->save){
                        throw new \think\Exception('月份不能小于'.date('Y年m月',$this->min));
                    }
                    if($this->max!==null&&$this->max<$this->save){
                        throw new \think\Exception('月份不能大于'.date('Y年m月',$this->max));
                    }
                }
            }else{
                $this->save=$this->nullVal();
                $this->defaultCheckRequired($this->nullVal(),'请选择月份');
            }
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
        $name=$this->name();
        if (isset($dataBaseData[$name])) {
            $dataBaseData[$name] = empty($dataBaseData[$name]) ? '' : \tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m',$dataBaseData[$name]);
        }
    }
    /**
     * 导出到excel时数据处理
     * @param array $data
     * @return string
     */
    public function getExportText(array $data): string
    {
        return empty($data[$this->name()]) ? '' : \tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m',$data[$this->name()]);
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


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tpscriptvuecurd/field/month/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeBigInt(10);
    }

}