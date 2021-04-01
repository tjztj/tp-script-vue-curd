<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\RadioFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\CheckField;


/**
 * 多选
 * Class CheckboxField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class CheckboxField extends ModelField
{
    use CheckField;

    protected string $defaultFilterClass=RadioFilter::class;

    //[
    //  [
    //      'value'=>'',
    //      'text'=>'',
    //      'group'=>'',
    //      'hideFields'=>[],
    //      'hide'=>false,//编辑页面中不显示此选项
    //   ]
    //]


    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        $name=$this->name();
        if(isset($data[$name])){
            if(empty($data[$name])) {
                $this->save='';
            }else {
                is_array($data[$name])||$data[$name]=explode(',',$data[$name]);
                $data[$name]=array_map('trim',$data[$name]);
                $data[$name]=array_filter($data[$name]);
                $otherVal=array_diff($data[$name],$this->getItemsValue());
                if($otherVal){
                    throw new \think\Exception(implode(',',$otherVal).' 不在可选中');
                }
                $this->save=implode(',',$data[$name]);
            }
        }
        //没有传参也要判断
        $this->defaultCheckRequired($this->save,'请选中正确的选项');
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
            $dataBaseData[$name]=$this->getShowText($dataBaseData[$name],true);
        }
    }


    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $str='请填入以下选项（可填写多个，换行来分隔）：';
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

        $items=$this->getItemsTextValues();
        $save[$name]=[];
        foreach (explode("\n",$save[$name]) as $v){
            $v=trim($v);
            if(empty($items[$v])){
                throw new \think\Exception($v.' 不在可选项中');
            }
            $save[$name][]=$items[$v];
        }
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tp-script-vue-curd-static.php?field/checkbox/edit.js')
        );
    }
}