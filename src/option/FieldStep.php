<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\ModelField;

class FieldStep
{

    private string $step;
    private string $title='';
    private ModelField $field;//相关字段名称，只有在字段中使用steps方法后获取到的 steps 才会有
    private $checkFunc;
    private $fieldCheckFunc;
    public array $config=[];//一些其他配置，如颜色

    public function __construct(string $step,callable $checkFunc,string $title='')
    {
        $this->step=$step;
        $this->checkFunc=$checkFunc;
        $this->title=$title;
    }


    public static function make(string $step,callable $check,string $title=''):self{
        return new self($step,$check,$title);
    }

    public function toArray():array{
        return [
            'step'=>$this->getStep(),
            'title'=>$this->getTitle(),
            'field'=>isset($this->field)&&!is_null($this->field)?$this->field->toArray():null,
            'config'=>$this->config,
        ];
    }

    public function getStep():string{
        return $this->step;
    }
    public function getTitle():string{
        return $this->title?:$this->getStep();
    }
    public function setTitle(string $title){
        $this->title=$title;
        return $this;
    }
    public function setField(ModelField $field):self{
        $this->field=&$field;
        return $this;
    }

    public function getField(): ?ModelField
    {
        return $this->field??null;
    }

    public function getFieldCheckFunc(){
        return $this->fieldCheckFunc??null;
    }

    public function setFieldCheckFunc(callable $fieldCheckFunc): self
    {
        $this->fieldCheckFunc=$fieldCheckFunc;
        return $this;
    }

    public function removeFieldData(): self
    {
        unset($this->field, $this->fieldCheckFunc);
        return $this;
    }


    /**
     * 步奏验证（是否到了这一步）
     * @param VueCurlModel $old  老的数据
     * @param array $new         新的数据（要修改为这样的数据）
     * @param bool $checkFieldFunc 如果为true，代表是验证当前字段是否满足这个step的条件
     * @return bool
     */
    public function check(VueCurlModel $old,array $new,bool $checkFieldFunc=false):bool{
        $check=$this->checkFunc;
        $rel=$check($old,$new);
        if(!$rel){
            return false;
        }
        if($checkFieldFunc&&isset($this->fieldCheckFunc)&&!is_null($this->fieldCheckFunc)){
            $fieldCheckFunc=$this->fieldCheckFunc;
            return $fieldCheckFunc($old,$new,$this->getField());
        }
        return true;
    }



}