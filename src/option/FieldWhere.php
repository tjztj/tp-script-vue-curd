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
            $valueData=(array)$valueData;
            $valueData=array_values($valueData);

            if(count($valueData)!==2){
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


    public function and(self $where):self{
        $this->ands[]=$where;
        return $this;
    }

    public function or(self $where):self{
        $this->ors[]=$where;
        return $this;
    }

    public function toArray():array{
        $field=clone $this->field;
        $field->objWellToArr=false;
        $arr=[
            'field'=>$field->toArray(),
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


    private function checkSelf($saveDatas,bool $isSourceData):bool{
        if($isSourceData){
            if($this->field->required()){
                $field=clone $this->field;
                $field->required(false);
            }else{
                $field= $this->field;
            }
            $field->setSave($saveDatas);
            $saveDatas[$this->field->name()]=$this->field->getSave();
        }

        if(!isset($saveDatas[$this->field->name()])||is_null($saveDatas[$this->field->name()])){
            return false;
        }
        $val=$saveDatas[$this->field->name()];

        if(is_array($val)){
            if($this->field->getType()==='RegionField'){
                $val=end($val);
            }else{
                throw new \think\Exception('配置错误');
            }
        }

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
     * @param array $saveDatas
     * @param bool $isSourceData 是否数据为源数据，未经过字段的setSave处理
     * @param ModelField[] $fields 相关的字段保存
     * @return bool
     * @throws \think\Exception
     */
    public function check(array $saveDatas,bool $isSourceData,array &$fields=[]):bool{
        $check=$this->checkSelf($saveDatas,$isSourceData);
        $fields[]=$this->field;

        if($check){
            foreach ($this->ands as $v){
                $fields[]=$v->field;
                if($v->check($saveDatas,$isSourceData)===false){
                    $check=false;
                    break;
                }
            }
        }

        if($check){
            return true;
        }

        foreach ($this->ors as $v){
            $fields[]=$v->field;
            if($v->check($saveDatas,$isSourceData)){
                return true;
            }
        }
        return false;
    }

}