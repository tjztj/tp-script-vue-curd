<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\ModelField;

class FieldWhere
{
    public const TYPE_IN='in';
    public const TYPE_BETWEEN='between';
    public const TYPE_FIND_IN_SET='find_in_set';

    //别名
    public const IN=self::TYPE_IN;
    public const BETWEEN=self::TYPE_BETWEEN;
    public const FIND_IN_SET=self::TYPE_FIND_IN_SET;



    private ModelField $field;
    private array $valueData;
    private string $type;
    private bool $isNot=false;

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
     * @param bool $isNot  是否非，反转
     * @throws \think\Exception
     */
    public function __construct(ModelField $field, $valueData, string $type=self::TYPE_IN, bool $isNot=false)
    {
        $this->field=$field;
        $this->type=$type;
        $this->isNot=$isNot;
        if($type===self::TYPE_IN){
            $this->valueData=(array)$valueData;
        }else if($type===self::TYPE_FIND_IN_SET){
            if($valueData===''||is_array($valueData)||is_null($valueData)){
                throw new \think\Exception('FieldWhere 参数错误-001');
            }
            $this->valueData=[(string)$valueData];
        }else{
            $valueData=(array)$valueData;
            $valueData=array_values($valueData);

            if(count($valueData)!==2){
                throw new \think\Exception('FieldWhere 参数错误-002');
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

    public static function make($field,$valueData=[],$type=self::TYPE_IN,bool $isNot=false):self{
        return new self($field,$valueData,$type,$isNot);
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
            'isNot'=>$this->isNot,
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


    private function checkSelf($saveDatas,bool $isSourceData,?VueCurlModel $info):bool{
        if(!$isSourceData){
            if($this->field->required()){
                $field=clone $this->field;
                $field->required(false);
            }else{
                $field= $this->field;
            }
            $field->setSave($saveDatas);
            $saveDatas[$this->field->name()]=$field->getSave();
        }
        if(!isset($saveDatas[$this->field->name()])){
            if(!$info||!isset($info[$this->field->name()])){
                return false;
            }
            $val=$info[$this->field->name()];
        }else{
            $val=$saveDatas[$this->field->name()];
        }

        if(is_null($val)){
            return false;
        }

        if(is_array($val)){
            if($this->field->getType()==='RegionField'){
                $val=end($val);
            }else{
                throw new \think\Exception('配置错误');
            }
        }

        return $this->checkVal($val)!==$this->isNot;
    }

    private function checkVal($val):bool{
        if($this->type===self::TYPE_IN){
            return in_array($val,$this->valueData);
        }
        if($this->type===self::TYPE_FIND_IN_SET){
            return in_array($this->valueData[0],is_array($val)?$val:explode(',',$val));
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
     * @param VueCurlModel|null $info  原数据
     * @return bool
     * @throws \think\Exception
     */
    public function check(array $saveDatas,bool $isSourceData,?VueCurlModel $info):bool{
        $check=$this->checkSelf($saveDatas,$isSourceData,$info);

        if($check){
            foreach ($this->ands as $v){
                if($v->check($saveDatas,$isSourceData,$info)===false){
                    $check=false;
                    break;
                }
            }
        }

        if($check){
            return true;
        }

        foreach ($this->ors as $v){
            if($v->check($saveDatas,$isSourceData,$info)){
                return true;
            }
        }
        return false;
    }

    public function getAboutFields():array{
        $fields=[];
        $fields[] = $this->field->name();
        foreach ($this->ands as $v){
            array_push($fields,...$v->getAboutFields());
        }
        foreach ($this->ors as $v){
            array_push($fields,...$v->getAboutFields());
        }
        return $fields;
    }

}