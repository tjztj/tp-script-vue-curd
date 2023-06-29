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


/**
 * 开关
 * Class RadioField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class SwitchField extends ModelField
{
    use CheckField{
        items as checkFieldItems;
    }

    protected string $defaultFilterClass=RadioFilter::class;

    protected string $indexChangeUrl='';//列表中更改开关时执行（如果设置为空字符串，列表中将只读）



    /**
     * 选项集合(获取与设置)
     * @param array|null $items
     * @return $this|array
     */
    public function items(array $items = null)
    {
        if(is_null($items)){
            return $this->items;
        }
        if(count($items)<2){
            throw new \think\Exception('设置的选项小于2个，不满足开关条件');
        }
        if(count($items)>2){
            throw new \think\Exception('设置的选项大于2个，超出开关条件');
        }

        return $this->checkFieldItems($items);
    }


    /**
     * 列表中开关状态改变时，要执行的url
     * @param callable|null $getDoUrl   请在这个函数中自行判断当前用户是否有执行权限，如果没有请返回空字符串，如果有，请返回url
     * @return $this|string
     */
    public function indexChangeUrl(callable $getDoUrl=null){
        if(is_null($getDoUrl)){
            return $this->indexChangeUrl;
        }
        $this->indexChangeUrl=$getDoUrl();
        $this->fieldPushAttrByWhere('indexChangeUrl',$this->indexChangeUrl);
        return $this;
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
        if(isset($dataBaseData[$name])){
            $dataBaseData[$name]=$this->getShowText($dataBaseData[$name],false);
        }
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
            new Index($type,'/tpscriptvuecurd/field/switch/index.js'),
            new Show($type,'/tpscriptvuecurd/field/switch/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/switch/edit.js')
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
            $option->setTypeInt(1);
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