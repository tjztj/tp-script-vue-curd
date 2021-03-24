<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\FieldCollection;

class FieldNumHideField
{

    protected $start;//开始 int或者float  或者等于null（无限小）
    protected $end;//结束 int或者float  或者等于null（无限大）
    protected FieldCollection $fields;//要隐藏的字段集合

    public static function init($start = null, $end = null, FieldCollection $fields = null): self
    {
        $self = new self();
        is_null($start) || $self->start = $start;
        is_null($end) || $self->end = $end;
        is_null($fields) || $self->fields = $fields;
        return $self;
    }

    public function toArray(): array
    {
        return [
            'start' => $this->getStart(),
            'end' => $this->getEnd(),
            'fields' => $this->getFields()->toArray(),
        ];
    }


    public function setStart($start): self
    {
        $this->start = $start;
        return $this;
    }

    public function getStart()
    {
        return $this->start ?? null;
    }

    public function setEnd($end): self
    {
        $this->end = $end;
        return $this;
    }

    public function getEnd()
    {
        return $this->end ?? null;
    }

    public function setFields(FieldCollection $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function getFields(): FieldCollection
    {
        return $this->fields;
    }

}