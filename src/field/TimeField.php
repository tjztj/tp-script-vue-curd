<?php

namespace tpScriptVueCurd\field;

use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\TimeFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

/**
 * 时间选择字段
 */
class TimeField extends ModelField
{
    protected string $defaultFilterClass=TimeFilter::class;
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->width=12;
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tpscriptvuecurd/field/time/edit.js')
        );
    }

    public function getGenerateColumnConfig(GenerateColumnOption $option): void
    {
        $option->setTypeVarchar(8);
    }
}