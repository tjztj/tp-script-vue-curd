<?php


namespace tpScriptVueCurd\tool\field_tpl;


class Show
{
    public string $name;
    public FieldTpl $fieldTpl;

    public function __construct(FieldTpl $fieldTpl)
    {
        $this->name='VueCurdShow'.$fieldTpl->name;
        $this->fieldTpl=$fieldTpl;
    }
}