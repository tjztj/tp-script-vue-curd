<?php


namespace tpScriptVueCurd\field;


use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class EditorField extends ModelField
{
    protected array $toolbar= [
        'head',
        'bold',
        'fontSize',
        'fontName',
        'italic',
        'underline',
        'strikeThrough',
        'indent',
        'lineHeight',
        'foreColor',
        'backColor',
        'link',
        'list',
        'todo',
        'justify',
        'quote',
        'emoticon',
        'image',
//        'video',
        'table',
//        'code',
        'splitLine',
        'undo',
        'redo',
        'editHtml',
    ];

    protected string $uploadUrl='';

    protected int $height=300;
    protected int $zIndex=0;//编辑器基本的zIndex



    protected bool $canExcelImport=false;//不能使用excel导入数据
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->explain='此列不可导入数据';
    }


    /**
     * 图片上传路径
     * @param string|null $url
     * @return $this|string
     */
    public function uploadUrl(string $url = null)
    {
        if(is_null($url)){
            return $this->uploadUrl?:tpScriptVueCurdUploadDefaultUrl();
        }
        $this->uploadUrl=$url;
        return $this;
    }

    /**
     * 工具栏显示按钮
     * @param bool|null $toolbar
     * @return $this|bool
     */
    public function toolbar(bool $toolbar=null){
        return $this->doAttr('toolbar',$toolbar);
    }

    public function doShowData(array &$dataBaseData): void
    {
        if(isset($dataBaseData[$this->name()])){
            $dataBaseData[$this->name()]=htmlspecialchars_decode($dataBaseData[$this->name()]);
        }
    }


    /**
     * 设置高度
     * @param int|null $height
     * @return $this|int
     */
    public function height(int $height=null){
        return $this->doAttr('height',$height);
    }

    /**
     * 编辑器基本的zIndex
     * @param int|null $zIndex
     * @return EditorField
     */
    public function zIndex(int $zIndex=null){
        return $this->doAttr('zIndex',$zIndex);
    }


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,'/tp-script-vue-curd-static.php?field/editor/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/editor/edit.js')
        );
    }
}