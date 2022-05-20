<?php

namespace tpScriptVueCurd\option\index_row_btn;

class Btn
{
    public string $btnTitle='';
    public string $btnColor='';

    public string $modalW='45vw';
    public string $modalH='100vh';
    public string $modalTitle='';
    public string $modalOffset='';//r,rt,rb,l,lt,lb,auto


    public function toArray():array{
        return [
            'btnTitle'=>$this->btnTitle,
            'btnColor'=>$this->btnColor,

            'modalW'=>$this->modalW,
            'modalH'=>$this->modalH,
            'modalTitle'=>$this->modalTitle?:$this->btnTitle,
            'modalOffset'=>$this->modalOffset,
        ];
    }
}