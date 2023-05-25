<?php


namespace tpScriptVueCurd\option;


class FieldStepTag
{
    //'red' | 'orangered' | 'orange' | 'gold' | 'lime' | 'green' | 'cyan' | 'blue' | 'arcoblue' | 'purple' | 'pinkpurple' | 'magenta' | 'gray'
    public string $color='';
    public string $text='';//显示的内容

    public function __construct(string $text='',string $color='')
    {
        if($text){
            $this->text=$text;
        }
        if($color){
            $this->color=$color;
        }
    }

    public function toArray(){
        return [
            'color'=>$this->color,
            'text'=>$this->text
        ];
    }
}