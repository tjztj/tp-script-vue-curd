<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\BetweenFilter;
use tpScriptVueCurd\ModelField;
use think\facade\Validate;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\NumHideFields;


/**
 * 整数
 * Class IntField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class IntField extends ModelField
{
    use NumHideFields;
    protected int $min = 0;//最小值
    protected int $max = 9999999999;//最大值
    protected int $listColumnWidth=85;//设置默认值
    protected string $defaultFilterClass=BetweenFilter::class;
    protected $nullVal=0;//字段在数据库中为空时的值


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

    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        $name=$this->name();
        if(isset($data[$name])){
            $data[$name]=trim($data[$name]);
            if($this->required()&&$data[$name]===''){
//                $this->defaultCheckRequired($this->save);
                throw new \think\Exception('不能为空');
            }
            if($data[$name]){
                $rule=['between'=>$this->min().','.$this->max()];
                $rule[]='integer';
                $validate=Validate::rule($name,$rule);
                if(!$validate->check($data)){
                    throw new \think\Exception($validate->getError());
                }
                $this->save=(int)$data[$name];
            }else{
                $this->save=$this->nullVal();
            }
        }else{
            if($this->min>0||$this->max<0){
                $this->defaultCheckRequired($this->nullVal());
            }
        }
        return $this;
    }


    /**
     * 模板导入时备注
     * @param ExcelFieldTpl $excelFieldTpl
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->explain="填入整数";
        $excelFieldTpl->width=12;
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tp-script-vue-curd-static.php?field/int/edit.js')
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