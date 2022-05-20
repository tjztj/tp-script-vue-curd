<?php

namespace tpScriptVueCurd\option\index_row_btn;

class OpenBtn extends Btn
{
    public string $modalUrl='';

    public function toArray():array{
        return [
            ...parent::toArray(),
            'modalUrl'=>$this->modalUrl,
        ];
    }
}