<?php


namespace tpScriptVueCurd\option;


use think\Collection;

class FieldStepCollection extends Collection
{

    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value->toArray();
        }, $this->items);
    }
}