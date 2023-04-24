<?php

namespace tpScriptVueCurd\option\field\edit_on_change\type;

use tpScriptVueCurd\option\field\edit_on_change\EditOnChange;

class Func extends EditOnChange
{
    /**
     * 编辑
     * @var callable
     */
    public $func;

    public string $url='';

    public function arr(): array
    {
        return [
            'url'=>$this->url?:request()->url(),
        ];
    }
}