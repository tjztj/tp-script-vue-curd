<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\RadioFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\CheckField;
use tpScriptVueCurd\traits\field\ListEdit;


/**
 * 单选
 * Class RadioField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class RadioField extends ModelField
{
    use CheckField,ListEdit;

    protected string $defaultFilterClass=RadioFilter::class;

    //[
    //  [
    //      'value'=>'',
    //      'text'=>'',
    //      'group'=>'',
    //      'hideFields'=>[],
    //      'hide'=>false,//编辑页面中不显示此选项
    //      'color'=>'编辑列表查看选项颜色'
    //   ]
    //]

    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        $name=$this->name();
        if(isset($data[$name])){
            if($data[$name]==='') {
                $this->defaultCheckRequired('','请选择正确的选项');
                $this->save=$this->nullVal();
            }else {
                if(!isset($this->getItemsValueTexts()[$data[$name]])){
                    if(empty($data[$name])){
                        $this->defaultCheckRequired('','请选择正确的选项');
                        $this->save=$this->nullVal();
                    }else{
                        throw new \think\Exception($data[$name].' 不在可选中');
                    }
                }else{
                    $this->save=$data[$name];
                }
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
        if(!isset($dataBaseData[$name])){
            return;
        }
        //如果值等于0，并且选项中没有等于0的选项，设置显示为空
        if(($dataBaseData[$name]===0||$dataBaseData[$name]==='0')&&!$this->haveZeroValue()){
            $dataBaseData[$name]='';
            return;
        }
        $dataBaseData[$name]=$this->getShowText($dataBaseData[$name],false);
    }
    /**
     * 导出到excel时数据处理
     * @param array $data
     * @return string
     */
    public function getExportText(array $data): string
    {
        $name=$this->name();
        if(!isset($data[$name])){
            return '';
        }
        //如果值等于0，并且选项中没有等于0的选项，设置显示为空
        if(($data[$name]===0||$data[$name]==='0')&&!$this->haveZeroValue()){
            return '';
        }
        return $this->getShowText($data[$name],false);
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $str='请填入以下选项：';
        $str.="\n";
        $texts=[];
        foreach ($this->items as $v){
            $texts[]=$v['text'];
        }
        $str.=implode("\n",$texts);

        $excelFieldTpl->explain=$str;
        $excelFieldTpl->wrapText=true;
        $excelFieldTpl->width=40;
    }
    /**
     * EXCEL导入时，对数据的处理
     * @param array $save
     */
    public function excelSaveDoData(array &$save):void{
        $name=$this->name();
        if(!isset($save[$name])){
            return;
        }
        if(trim($save[$name])===''&&$this->required()===false){
            return;
        }
        $values=$this->getItemsTextValues();

        if(!isset($values[$save[$name]])){
            throw new \think\Exception($save[$name].' 不在可选中');
        }

        $save[$name]=$values[$save[$name]];
    }


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tpscriptvuecurd/field/radio/index.js'),
            new Show($type,'/tpscriptvuecurd/field/radio/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/radio/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $isNumVal=true;
        foreach ($this->items() as $v){
            if(!is_numeric($v['value'])){
                $isNumVal=false;
                break;
            }
        }
        if($isNumVal){
            $option->setTypeInt();
        }else{
            $option->setTypeVarchar(64);
        }
    }


    /**
     * Excel 模版中的下拉选项
     * @return array
     */
    public function excelSelectItems()
    {
        return array_column($this->items(),'text');
    }
}