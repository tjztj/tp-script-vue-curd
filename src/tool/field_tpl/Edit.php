<?php


namespace tpScriptVueCurd\tool\field_tpl;


class Edit
{
    public string $name;
    public string $jsUrl;
    public FieldTpl $fieldTpl;

    public function __construct(FieldTpl $fieldTpl)
    {
        $this->name='VueCurdEdit'.$fieldTpl->name;
        $this->fieldTpl=$fieldTpl;
    }

}