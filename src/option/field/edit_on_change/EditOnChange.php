<?php

namespace tpScriptVueCurd\option\field\edit_on_change;

abstract class EditOnChange
{

    abstract public function arr():array;

    public function toArray():array{
        $arr=$this->arr();
        $arr['type']=class_basename($this);
        return $arr;
    }
}