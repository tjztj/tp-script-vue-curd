<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\SelectFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\CheckField;
use tpScriptVueCurd\traits\field\ListEdit;


/**
 * Select下拉选择
 * Class SelectField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class SelectField extends ModelField
{
    use CheckField,ListEdit;

    protected bool $multiple=false;//是否可多选
    protected string $defaultFilterClass=SelectFilter::class;


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
     * 是否可多选
     * @param bool|null $multiple
     * @return $this|bool
     */
    public function multiple(bool $multiple=null){
        return $this->doAttr('multiple',$multiple);
    }


    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        $name=$this->name();
        if(isset($data[$name])){
            is_array($data[$name])||$data[$name]=explode(',',$data[$name]);
            $data[$name]=array_map('trim',$data[$name]);
            $data[$name]=array_filter($data[$name]);
            $otherVal=array_diff($data[$name],$this->getItemsValue());
            if($otherVal){
                throw new \think\Exception(implode(',',$otherVal).' 不在可选中');
            }
            if($this->multiple()===false&&count($data[$name])>1){
                throw new \think\Exception('只能选择一项');
            }
            $this->save=implode(',',$data[$name]);
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
        $name=$this->name();
        if(!isset($dataBaseData[$name])){
            return;
        }
        //如果值等于0，并且选项中没有等于0的选项，设置显示为空
        if(($dataBaseData[$name]===0||$dataBaseData[$name]==='0')&&!$this->haveZeroValue()){
            $dataBaseData[$name]='';
            return;
        }
        $dataBaseData[$name]=$this->getShowText($dataBaseData[$name],$this->multiple);
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
        return $this->getShowText($data[$name],$this->multiple);
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $str='请填入以下选项'.($this->multiple?'（可填写多个，换行来分隔）':'').'：';
        $str.="\n";
        $texts=[];
        foreach ($this->items as $v){
            $texts[]=$v['text'];
        }
        $str.=implode("\n",$texts);


        $excelFieldTpl->explain=$str;
        $excelFieldTpl->width=40;
        $excelFieldTpl->wrapText=true;
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

        $arr=explode("\n",$save[$name]);
        $values=$this->getItemsTextValues();
        foreach ($arr as $k=>$v){
            $v=trim($v);
            if($v===''){
                continue;
            }

            if(isset($values[$v])){
                $arr[$k]=$values[$v];
            }else{
                throw new \think\Exception($v.' 不在可选项中');
            }
        }
        $save[$name]=$arr;
    }


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tpscriptvuecurd/field/select/index.js'),
            new Show($type,'/tpscriptvuecurd/field/select/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/select/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        if($this->multiple){
            $option->setTypeVarchar(64);
        }else{
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

    }
    /**
     * Excel 模版中的下拉选项
     * @return array
     */
    public function excelSelectItems()
    {
        if($this->multiple())
            return [];
        return array_column($this->items(),'text');
    }
}