<?php


namespace tpScriptVueCurd\tool\field_tpl;


class Index
{
    public string $name;
    public FieldTpl $fieldTpl;

    public function __construct(FieldTpl $fieldTpl)
    {
        $this->name='VueCurdIndex'.$fieldTpl->name;
        $this->fieldTpl=$fieldTpl;
    }
}