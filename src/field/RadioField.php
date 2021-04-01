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
 * 单选
 * Class RadioField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class RadioField extends ModelField
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
    //      'color'=>'编辑列表查看选项颜色'
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
            if($data[$name]==='') {
                $this->defaultCheckRequired('','请选择正确的选项');
                $this->save='';
            }else {
                if(!isset($this->getItemsValueTexts()[$data[$name]])){
                    throw new \think\Exception($data[$name].' 不在可选中');
                }
                $this->save=$data[$name];
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
        if(isset($dataBaseData[$name])){
            $dataBaseData[$name]=$this->getShowText($dataBaseData[$name],false);
        }
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


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tp-script-vue-curd-static.php?field/radio/index.js'),
            new Show($type,'/tp-script-vue-curd-static.php?field/radio/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/radio/edit.js')
        );
    }

}