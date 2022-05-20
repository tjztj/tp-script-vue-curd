<?php

namespace tpScriptVueCurd\option\index_row_btn;

class Btn
{
    public string $btnTitle='';
    public string $btnType='a';//''（空）；'a'；'danger'；'warning'；'info'；'primary'；'dashed'；'text'；'link'
    public string $btnColor='';//btnType==='a'有效
    public string $btnSvg='';//按钮图片，特定情况下有效

    public string $modalW='45vw';
    public string $modalH='100vh';
    public string $modalTitle='';
    public string $modalOffset='';//r,rt,rb,l,lt,lb,auto


    public function toArray():array{
        return [
            'selfType'=>class_basename(static::class),

            'btnTitle'=>$this->btnTitle,
            'btnType'=>$this->btnType,
            'btnColor'=>$this->btnColor,
            'btnSvg'=>$this->btnSvg,

            'modalW'=>$this->modalW,
            'modalH'=>$this->modalH,
            'modalTitle'=>$this->modalTitle?:$this->btnTitle,
            'modalOffset'=>$this->modalOffset,
        ];
    }
}