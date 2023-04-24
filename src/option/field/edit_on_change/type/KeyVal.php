<?php

namespace tpScriptVueCurd\option\field\edit_on_change\type;

use tpScriptVueCurd\option\field\edit_on_change\EditOnChange;
use tpScriptVueCurd\option\FieldWhere;

class KeyVal extends EditOnChange
{
    /**
     * 示例1：form.title
     * 示例2：fields.title.required
     * @var string
     */
    public string $key='';

    /**
     *
     * @var string|bool|int|array
     */
    public $val;

    public ?FieldWhere $where=null;

    public function arr(): array
    {
        return [
            'key'=>$this->key,
            'val'=>$this->val,
            'where'=>$this->where?$this->where->toArray():null,
        ];
    }
}