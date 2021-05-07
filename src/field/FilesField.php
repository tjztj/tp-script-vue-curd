<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;


/**
 * 文件
 * Class FilesField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class FilesField extends ModelField
{

    protected string $url='';//默认值是 tpScriptVueCurdUploadDefaultUrl
    protected bool $canExcelImport=false;//不能使用excel导入数据
    protected string $accept='';//上传文件类型


    /**最小值
     * @param string|null $url
     * @return $this|string
     */
    public function url(string $url = null)
    {
        if(is_null($url)){
            return $this->url?:tpScriptVueCurdUploadDefaultUrl();
        }
        $this->url=$url;
        return $this;
    }

    /**
     * 文件上传类型
     * @param string|null $accept
     * @return $this|string
     */
    public function accept(string $accept=null){
        return $this->doAttr('accept',$accept);
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
        $this->defaultCheckRequired($this->save,'请上传附件');
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
        $excelFieldTpl->explain="此列不可导入数据";
    }


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tp-script-vue-curd-static.php?field/files/index.js'),
            new Show($type,'/tp-script-vue-curd-static.php?field/files/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/files/edit.js')
        );
    }

}