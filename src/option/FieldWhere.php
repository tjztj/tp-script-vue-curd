<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\ModelField;

class FieldWhere
{
    const TYPE_IN='in';
    const TYPE_BETWEEN='between';

    private ModelField $field;
    private array $valueData;
    private string $type;

    /**
     * 其他并且条件
     * @var static[]
     */
    private array $ands=[];

    /**
     * 其他或者条件
     * @var static[]
     */
    private array $ors=[];

    /**
     * FieldWhere constructor.
     * @param ModelField $field
     * @param array|string|int|float $valueData
     * @param string $type
     */
    public function __construct(ModelField $field,$valueData,$type=self::TYPE_IN)
    {
        $this->field=$field;
        $this->type=$type;
        if($type===self::TYPE_IN){
            $this->valueData=(array)$valueData;
        }else{
            if(!isset($valueData[0])||!isset($valueData[1])){
                throw new \think\Exception('FieldWhere 参数错误');
            }
            if(is_null($valueData[0])&&is_null($valueData[1])){
                throw new \think\Exception('FieldWhere 参数格式不正确-001');
            }
            if((!is_numeric($valueData[0])&&!is_null($valueData[0]))||(!is_numeric($valueData[1])&&!is_null($valueData[1]))){
                throw new \think\Exception('FieldWhere 参数格式不正确-002');
            }
            $this->valueData=[$valueData[0],$valueData[1]];
        }
    }

    public static function make(ModelField $field,$valueData,$type=self::TYPE_IN):self{
        return new self($field,$valueData,$type);
    }


    public function and(self $where){
        $this->ands[]=$where;
    }

    public function or(self $where){
        $this->or();
    }

    public function toArray(){
        $arr=[
            'field'=>$this->field->toArray(),
            'valueData'=>$this->valueData,
            'type'=>$this->type,
        ];
        $ands=[];
        foreach ($this->ands as $v){
            $ands[]=$v->toArray();
        }
        $ors=[];
        foreach ($this->ors as $v){
            $ors[]=$v->toArray();
        }
        $arr['ands']=$ands;
        $arr['ors']=$ors;

        return $arr;
    }


    public function checkSelf($saveDatas):bool{
        if(!isset($saveDatas[$field])){
            return false;
        }
        $val=$saveDatas[$field];

        if($this->type===self::TYPE_IN){
            return in_array($val,$this->valueData);
        }
        if(is_null($this->valueData[0])){
            return $val<=$this->valueData[1];
        }

        if(is_null($this->valueData[1])){
            return $val>=$this->valueData[0];
        }

        return $val<=$this->valueData[1]&&$val>=$this->valueData[0];
    }


    /**
     * 验证数据是否符合条件
     * @param $saveDatas
     * @return bool
     */
    public function check($saveDatas):bool{
        $check=$this->checkSelf($saveDatas);

        if($check){
            foreach ($this->ands as $v){
                if($v->checkSelf($saveDatas)===false){
                    $check=false;
                    break;
                }
            }
        }

        if($check){
            return true;
        }

        foreach ($this->ors as $v){
            if($v->checkSelf($saveDatas)){
                return true;
            }
        }
        return false;
    }

}