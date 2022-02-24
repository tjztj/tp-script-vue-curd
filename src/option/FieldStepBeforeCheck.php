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
    public $func;


    /**
     * @param string $remark 当前步骤必须要写说明，方便阅读
     * @param callable $func
     */
    public function __construct(string $remark,callable $func)
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
    public static function make(string $remark,callable $func): FieldStepBeforeCheck
    {
        return new self($remark,$func);
    }
}