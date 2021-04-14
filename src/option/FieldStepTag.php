<?php


namespace tpScriptVueCurd\option;


class FieldStepTag
{

    public string $color='default';//pink、red（或error）、orange（或warning）、green（或success）、cyan、blue（或processing）、purple
    public string $text='';//显示的内容


    public function toArray(){
        return [
            'color'=>$this->color,
            'text'=>$this->text
        ];
    }
}