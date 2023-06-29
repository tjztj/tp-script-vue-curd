<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\LikeFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\ListEdit;


/**
 * 长字符串
 * Class TextField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class TextField extends ModelField
{
    use ListEdit;
    protected string $defaultFilterClass=LikeFilter::class;

    protected int $rowMin = 2;
    protected int $rowMax = 5;
    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        if(isset($data[$this->name()])){
            $this->save=trim($data[$this->name()]);
            if($this->required()&&($this->save===''||$this->save===$this->nullVal())){
                $this->defaultCheckRequired($this->save);
            }
        }else{
            $this->defaultCheckRequired($this->nullVal());
        }
        return $this;
    }

    public function rowMin($min = null)
    {
        return $this->doAttr('rowMin', $min);
    }

    public function rowMax($max = null)
    {
        return $this->doAttr('rowMax', $max);
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->isText=true;
        $excelFieldTpl->wrapText=true;
        $excelFieldTpl->width=40;
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tpscriptvuecurd/field/text/index.js'),
            new Show($type,'/tpscriptvuecurd/field/text/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/text/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeText();
    }
}