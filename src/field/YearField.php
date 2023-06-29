<?php

namespace tpScriptVueCurd\field;

use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\YearFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class YearField extends ModelField
{
    protected string $defaultFilterClass=YearFilter::class;
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->width=8;
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tpscriptvuecurd/field/year/edit.js')
        );
    }

    public function getGenerateColumnConfig(GenerateColumnOption $option): void
    {
        $option->setTypeInt(4);
    }
}