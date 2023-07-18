<?php


namespace tpScriptVueCurd\field;


use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class IframeField extends ModelField
{

    protected string $defaultFilterClass = EmptyFilter::class;

//    protected bool $showPage = false;
    protected bool $canExcelImport = false;

    protected string $topHrText='';
    protected string $bottomHrText='';

    protected string $url='';

    protected $generateColumn=false;

    public function __construct()
    {
        parent::__construct();
        $this->editWrapperCol(['span'=>24]);
    }

    /**
     * 模板导入时备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->explain = '不可导入';
    }

    /**
     * 字段模板配置
     * @return FieldTpl
     */
    public static function componentUrl(): FieldTpl
    {
        $type = class_basename(static::class);
        return new FieldTpl($type,
            new Index($type, ''),
            new Show($type, '/tpscriptvuecurd/field/iframe/show.js'),
            new Edit($type, '/tpscriptvuecurd/field/iframe/edit.js')
        );
    }

    /**
     * @param string|\think\route\Url $url
     * @return IframeField|string
     */
    public function url($url=null){
        if($url instanceof \think\route\Url){
            $url=$url->build();
        }
        return $this->doAttr('url',$url);
    }

    public function setCanExcelImport(bool $canExcelImport): void
    {
        if ($canExcelImport === true) {
            throw new \think\Exception('字段不可设置为导入');
        }
        $this->canExcelImport = $canExcelImport;
    }


    public function listShow(bool $listShow = null)
    {
        if ($listShow === true) {
            throw new \think\Exception('字段不可设置$listShow为true');
        }
        return parent::listShow($listShow);
    }

    public function topHrText($topHrText=null){
        return $this->doAttr('topHrText',$topHrText);
    }

    public function bottomHrText($bottomHrText=null){
        return $this->doAttr('bottomHrText',$bottomHrText);
    }

    public function getGenerateColumnConfig(GenerateColumnOption $option): void
    {
        $option->setTypeVarchar();
    }

    public function toArray(): array
    {
        if($this->title===''){
            $this->showUseComponent(true);
        }
        return parent::toArray();
    }
}