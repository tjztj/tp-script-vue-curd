<?php


namespace tpScriptVueCurd\tool\field_tpl;


class Show
{
    public string $name;
    public string $jsUrl;

    public function __construct(string $fieldType,string $jsUrl)
    {
        $this->name='VueCurdIndex'.$fieldType;
        $this->jsUrl=$jsUrl;
    }
}