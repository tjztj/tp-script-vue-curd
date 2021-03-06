<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;


/**
 * 图片
 * Class ImagesField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class ImagesField extends ModelField
{

    protected string $url='';


    /**最小值
     * @param string|null $url
     * @return $this|string
     */
    public function url(string $url = null)
    {
        if(is_null($url)){
            return $this->url?:uploadDefaultUrl();
        }
        $this->url=$url;
        return $this;
    }

    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        if(isset($data[$this->name()])){
            $this->save=$data[$this->name()];
        }
        $this->defaultCheckRequired($this->save,'请上传图片');
        return $this;
    }


    /**
     * 显示时对数据处理
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        parent::doShowData($dataBaseData);
        if(isset($dataBaseData[$this->name()])){
            $dataBaseData[$this->name()]=trim($dataBaseData[$this->name()]);
            $dataBaseData[$this->name().'Arr']=$dataBaseData[$this->name()]?explode('|',$dataBaseData[$this->name()]):[];
        }
    }


    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->explain="插入相关图片，可插入多张；\n请将插入的图片缩小到单元格内；\n竖向合并单元格的行将共用图片；\n必看！！".url('index/imgExplain',[],true,true)->build().'；';
        $excelFieldTpl->wrapText=true;
        $excelFieldTpl->width=42;
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
        $save[$name]=empty($save[$name])?'':implode('|',array_map(fn($vo)=>str_replace(public_path(), request()->domain().DIRECTORY_SEPARATOR, $vo),$save[$name]));
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tp-script-vue-curd-static.php?field/images/index.js'),
            new Show($type,'/tp-script-vue-curd-static.php?field/images/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/images/edit.js')
        );
    }
}