<?php

namespace tpScriptVueCurd\field;

use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\BetweenFilter;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class ColorField extends ModelField
{
    protected string $defaultFilterClass=EmptyFilter::class;
    protected $nullVal='';//字段在数据库中为空时的值


    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->explain='请输入HEX颜色代码';
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tpscriptvuecurd/field/color/index.js'),
            new Show($type,'/tpscriptvuecurd/field/color/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/color/edit.js')
        );
    }

    public function getGenerateColumnConfig(GenerateColumnOption $option): void
    {
        $option->setTypeVarchar(9);
    }
}