<?php

namespace tpScriptVueCurd\option\index_row_btn;

class OpenBtn extends Btn
{
    public string $modalUrl='';

    public function toArray():array{
        $btn=parent::toArray();
        $btn['modalUrl']=$this->modalUrl;
        return $btn;
    }
}