<?php


namespace tpScriptVueCurd\tool\field_tpl;


class FieldTpl
{

    public string $name;

    public Index $index;

    public Show $show;

    public Edit $edit;

    public function __construct(string $fieldType)
    {
        $this->name=$fieldType;
    }

}