<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\ModelField;

class StepCheck
{

    private $beforeCheck;//数据下一步是否到我这里，如果是，返回true
    private $check;//数据是否符合当前步骤

    public function __construct(callable $beforeCheck,$check=null)
    {
        $this->beforeCheck=$beforeCheck;
        $this->check=$check;
    }


    public static function make(callable $beforeCheck,$check=null){
        return new self($beforeCheck,$check);
    }


    /**
     * 验证数据
     * @param VueCurlModel|null $info
     * @param BaseModel|null $baseInfo
     * @param ModelField|null $field
     * @return mixed
     */
    public function beforeCheck(VueCurlModel $info=null,BaseModel $baseInfo=null,ModelField $field=null){
        $func=$this->beforeCheck;
        return $func($info,$baseInfo,$field);
    }


    /**
     * 验证数据是否符合当前步骤
     * @param VueCurlModel|null $old
     * @param BaseModel|null $baseInfo
     * @param ModelField|null $field
     * @return mixed
     */
    public function check(VueCurlModel $old=null,BaseModel $baseInfo=null,ModelField $field=null){
        if(!isset($this->check)||is_null($this->check)){
            throw new \think\Exception('未设置check');
        }
        $func=$this->check;
        return $func($old,$baseInfo,$field);
    }



    /**
     * 当未设置 check 的时候，可以设置为 数据步骤与 步奏对象的值相同就为true
     * @param string $step
     */
    public function whenEmptyCheckSetByStep(string $step){
        if(!isset($this->check)||is_null($this->check)){
            $this->check=function(VueCurlModel $old=null)use($step){
                if(!$old||empty($data->id)){
                    return false;
                }
                return eqEndStep($step,$old);
            };
        }
        return $this;
    }
}