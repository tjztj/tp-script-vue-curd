<?php

namespace tpScriptVueCurd\option\field\edit_on_change\type;

use tpScriptVueCurd\option\field\edit_on_change\EditOnChange;

class Url extends EditOnChange
{
    public string $url='';

    public function arr(): array
    {
        return [
            'url'=>$this->url
        ];
    }
}