<?php


namespace tpScriptVueCurd\tool\field_tpl;


class FieldTpl
{

    public string $name;

    public Index $index;

    public Show $show;

    public Edit $edit;

    public function __construct(string $fieldType,Index $index,Show $show,Edit $edit)
    {
        $this->name=$fieldType;
        $this->index=$index;
        $this->show=$show;
        $this->edit=$edit;
    }

    public function toArray($obj=null){
        return $this->objectToArray(is_null($obj)?$this:$obj);
    }

    private function objectToArray($array) {
        if(is_array($array)){
            $arr=$array;
        }else if(is_object($array)) {
            $arr = get_object_vars($array);
        }else{
            return $array;
        }
        foreach($arr as $key=>$value) {
            $arr[$key] = $this->objectToArray($value);
        }
        return $arr;
    }
}