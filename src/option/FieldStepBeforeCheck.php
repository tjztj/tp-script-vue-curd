<?php

namespace tpScriptVueCurd\option;

class FieldStepBeforeCheck
{
    /**
     * 逻辑说明
     * @var string
     */
    public string $remark='';

    /**
     * 逻辑处理
     * @var callable
     */
    public $func=null;



    public function __construct(callable $func,string $remark='')
    {
        $this->remark=$remark;
        $this->func=$func;
    }

    /**
     * 初始化函数
     * @param callable $func 逻辑处理
     * @param string $remark 逻辑说明
     * @return FieldStepBeforeCheck
     */
    public static function make(callable $func,string $remark=''){
        $self=new self($func,$remark);
        return $self;
    }
}