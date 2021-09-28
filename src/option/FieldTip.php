<?php

namespace tpScriptVueCurd\option;

/**
 * 字段编辑和显示时，顶部显示的提示
 */
class FieldTip
{

    public ?FieldWhere $show;

    public string $message;

    public string $title='';

    public string $type='warning'; //success,info,warning,error

    public bool $closable=false;//是否显示关闭按钮

    public bool $showIcon=true;//是否显示图标

    public bool $border=false;//是否显示边框


    private string $guid;


    public function __construct(string $message,?FieldWhere $show)
    {
        $this->show=$show;
        $this->message=$message;
        $this->guid=create_guid();
    }


    public function toArray():array
    {
        return [
            'show'=>$this->show?$this->show->toArray():null,
            'message'=>$this->message,
            'title'=>$this->title,
            'type'=>$this->type,
            'closable'=>$this->closable,
            'showIcon'=>$this->showIcon,
            'border'=>$this->border,
            'guid'=>$this->guid,
        ];
    }

    /**
     * @return string
     */
    public function getGuid(): string
    {
        return $this->guid;
    }
}