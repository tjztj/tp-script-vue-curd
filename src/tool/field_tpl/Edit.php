<?php


namespace tpScriptVueCurd\tool\field_tpl;


class Edit
{
    public string $name;
    public string $jsUrl;

    public function __construct(string $fieldType,string $jsUrl)
    {
        $this->name='VueCurdIndex'.$fieldType;
        $this->jsUrl=$jsUrl;
    }

}