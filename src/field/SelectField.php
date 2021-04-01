<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\SelectFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\CheckField;


/**
 * Select下拉选择
 * Class SelectField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class SelectField extends ModelField
{
    use CheckField;

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
    public function setSaveVal(array $data): self
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
        if(isset($dataBaseData[$name])){
            $dataBaseData[$name]=$this->getShowText($dataBaseData[$name],$this->multiple);
        }
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        if(count($this->items)>5){
            $str='请根据网站上的规则填入相关选项';
        }else{
            $str='请填入以下选项'.($this->multiple?'（可填写多个，换行来分隔）':'').'：';
            $str.="\n";
            $texts=[];
            foreach ($this->items as $v){
                $texts[]=$v['text'];
            }
            $str.=implode("\n",$texts);
        }


        $excelFieldTpl->explain=$str;
        $excelFieldTpl->width=40;
        $excelFieldTpl->wrapText=true;
    }



    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tp-script-vue-curd-static.php?field/select/index.js'),
            new Show($type,'/tp-script-vue-curd-static.php?field/select/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/select/edit.js')
        );
    }
}