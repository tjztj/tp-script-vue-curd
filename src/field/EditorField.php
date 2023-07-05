<?php


namespace tpScriptVueCurd\field;


use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class EditorField extends ModelField
{
    protected array $toolbar= [
        'headerSelect',
        // 'header1',
        // 'header2',.
        // 'header3',
        'blockquote',
        '|',
        'bold',
        'underline',
        'italic',
        [
            'key'=>'group-more-style',//以 group 开头
            'title'=>'更多',
            'iconSvg'=>'<svg viewBox="0 0 1024 1024"><path d="M959.877 128l0.123 0.123v767.775l-0.123 0.122H64.102l-0.122-0.122V128.123l0.122-0.123h895.775zM960 64H64C28.795 64 0 92.795 0 128v768c0 35.205 28.795 64 64 64h896c35.205 0 64-28.795 64-64V128c0-35.205-28.795-64-64-64zM832 288.01c0 53.023-42.988 96.01-96.01 96.01s-96.01-42.987-96.01-96.01S682.967 192 735.99 192 832 234.988 832 288.01zM896 832H128V704l224.01-384 256 320h64l224.01-192z"></path></svg>',
            'menuKeys'=>[
                'through',
//                'code',
                'sup',
                'sub',
                'clearStyle'
            ],
        ],
        'color',
        'bgColor',
        '|',
        'fontSize',
        'fontFamily',
        'lineHeight',
        '|',
        'bulletedList',
        'numberedList',
        'todo',
        [
            'key'=>'group-justify',//以 group 开头
            'title'=>'对齐',
            'iconSvg'=> '<svg viewBox="0 0 1024 1024"><path d="M768 793.6v102.4H51.2v-102.4h716.8z m204.8-230.4v102.4H51.2v-102.4h921.6z m-204.8-230.4v102.4H51.2v-102.4h716.8zM972.8 102.4v102.4H51.2V102.4h921.6z"></path></svg>',
            'menuKeys'=>['justifyLeft', 'justifyRight', 'justifyCenter', 'justifyJustify'],
        ],
        [
            'key'=>'group-indent',//以 group 开头
            'title'=>'缩进',
            'iconSvg'=> '<svg viewBox="0 0 1024 1024"><path d="M0 64h1024v128H0z m384 192h640v128H384z m0 192h640v128H384z m0 192h640v128H384zM0 832h1024v128H0z m0-128V320l256 192z"></path></svg>',
            'menuKeys'=>['indent', 'delIndent'],
        ],
        '|',
        'emotion',
        'insertLink',
        // 'editLink',
        // 'unLink',
        // 'viewLink',
        [
            'key'=>'group-image',//以 group 开头
            'title'=>'图片',
            'iconSvg'=>  '<svg viewBox="0 0 1024 1024"><path d="M959.877 128l0.123 0.123v767.775l-0.123 0.122H64.102l-0.122-0.122V128.123l0.122-0.123h895.775zM960 64H64C28.795 64 0 92.795 0 128v768c0 35.205 28.795 64 64 64h896c35.205 0 64-28.795 64-64V128c0-35.205-28.795-64-64-64zM832 288.01c0 53.023-42.988 96.01-96.01 96.01s-96.01-42.987-96.01-96.01S682.967 192 735.99 192 832 234.988 832 288.01zM896 832H128V704l224.01-384 256 320h64l224.01-192z"></path></svg>',
            'menuKeys'=>['insertImage', 'uploadImage'],
        ],
        // 'deleteImage',
        // 'editImage',
        // 'viewImageLink',
        [
            'key'=>'group-video',//以 group 开头
            'title'=>'视频',
            'iconSvg'=>  '<svg viewBox="0 0 1024 1024"><path d="M981.184 160.096C837.568 139.456 678.848 128 512 128S186.432 139.456 42.816 160.096C15.296 267.808 0 386.848 0 512s15.264 244.16 42.816 351.904C186.464 884.544 345.152 896 512 896s325.568-11.456 469.184-32.096C1008.704 756.192 1024 637.152 1024 512s-15.264-244.16-42.816-351.904zM384 704V320l320 192-320 192z"></path></svg>',
            'menuKeys'=>['insertVideo', 'uploadVideo'],
        ],
        // 'deleteVideo',
        'insertTable',
//        'codeBlock',
        // 'codeSelectLang',
        'divider',
        // 'deleteTable',
        '|',
        'undo',
        'redo',
        '|',
        'fullScreen',
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
     * @param array|null $toolbar
     * @return $this|bool
     */
    public function toolbar(array $toolbar=null){
        return $this->doAttr('toolbar',$toolbar);
    }

    /**
     * 数据显示时处理
     * @param array $dataBaseData
     * @return void
     */
    public function doShowData(array &$dataBaseData): void
    {
        $filter=request()->filter();
        if(isset($dataBaseData[$this->name()])&&((is_string($filter)&&strpos($filter,'htmlspecialchars')!==false)||(is_array($filter)&&in_array('htmlspecialchars',$filter)))){
            $dataBaseData[$this->name()]=htmlspecialchars_decode($dataBaseData[$this->name()]);
        }
    }
    /**
     * 导出到excel时数据处理
     * @param array $data
     * @return string
     */
    public function getExportText(array $data): string
    {
        if(!isset($data[$this->name()])){
            return '';
        }
        return strip_tags(htmlspecialchars_decode($data[$this->name()]));
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
            new Show($type,'/tpscriptvuecurd/field/editor/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/editor/edit.js')
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