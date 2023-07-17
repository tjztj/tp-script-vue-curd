<?php


namespace tpScriptVueCurd\option;


use think\db\Query;
use think\Model;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\field\StringField;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\traits\field_where\FieldWhereDump;

class FieldWhere
{
    use FieldWhereDump;

    public const TYPE_IN='in';
    public const TYPE_BETWEEN='between';
    public const TYPE_FIND_IN_SET='find_in_set';
    public const TYPE_GT='>';
    public const TYPE_EGT='>=';
    public const TYPE_LT='<';
    public const TYPE_ELT='<=';
    public const TYPE_EQ='=';

    //别名
    public const IN=self::TYPE_IN;
    public const BETWEEN=self::TYPE_BETWEEN;
    public const FIND_IN_SET=self::TYPE_FIND_IN_SET;
    public const NOT='__NOT__';
    public const NOT_IN=self::NOT;
    public const NOT_BETWEEN='__NOT_BETWEEN__';
    public const NOT_FIND_IN_SET='__NOT_FIND_IN_SET__';

    public const GT=self::TYPE_GT;
    public const EGT=self::TYPE_EGT;
    public const LT=self::TYPE_LT;
    public const ELT=self::TYPE_ELT;
    public const EQ=self::TYPE_EQ;




    public const RETURN_FALSE_FIELD_NAME='__InitializationFieldWhereField__';

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
     * @param array|string|int|float $valueData in,between,find_in_set,>,<,=,<>,>=,<=,!=
     * @param string $type
     * @param bool $isNot  是否非，反转
     * @throws \think\Exception
     */
    public function __construct(ModelField $field, $valueData, string $type=self::TYPE_IN, bool $isNot=false)
    {
//        $type=strtolower($type);
        switch ($type){
            case '<>':
            case '!=':
            case '!==':
            case self::NOT:
                $type=self::TYPE_IN;
                $isNot=!$isNot;
                break;
            case self::NOT_BETWEEN:
                $type=self::TYPE_BETWEEN;
                $isNot=!$isNot;
                break;
            case self::NOT_FIND_IN_SET:
                $type=self::TYPE_FIND_IN_SET;
                $isNot=!$isNot;
                break;
            case self::EGT:
                $type=self::TYPE_BETWEEN;
                $valueData=[$valueData,null];
                break;
            case self::GT:
                $type=self::TYPE_BETWEEN;
                $isNot=!$isNot;
                $valueData=[null,$valueData];
                break;
            case self::ELT:
                $type=self::TYPE_BETWEEN;
                $valueData=[null,$valueData];
                break;
            case self::LT:
                $type=self::TYPE_BETWEEN;
                $isNot=!$isNot;
                $valueData=[$valueData,null];
                break;
            case self::EQ:
                $type=self::TYPE_IN;
                $valueData=[$valueData];
                break;

        }


        $this->field=$field;
        $this->type=$type;
        $this->isNot=$isNot;
        if($type===self::TYPE_IN){
            $this->valueData=(array)$valueData;
        }else if($type===self::TYPE_FIND_IN_SET){
            if($valueData===''||is_null($valueData)){
                throw new \think\Exception('FieldWhere 参数错误-001');
            }
            if(is_array($valueData)){
                $valueData=array_map('strval',$valueData);
            }else{
                $valueData=[(string)$valueData];
            }
            $this->valueData=$valueData;
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

    /**
     * @param ModelField|string $field  可以直接写字段名，但不推荐
     * @param array|string|int|float $valueData
     * @param string $type
     * @param bool $isNot
     * @return self
     * @throws \think\Exception
     */
    public static function make($field,$valueData=[],string $type=self::TYPE_IN,bool $isNot=false):self{
        if(is_string($field)){
            $field=StringField::init($field);
        }
        return new self($field,$valueData,$type,$isNot);
    }


    /**
     * 初始化一个无条件的where
     * @return static
     */
    public static function init():self{
        return FieldWhere::make(StringField::init(FieldWhere::RETURN_FALSE_FIELD_NAME), FieldWhere::RETURN_FALSE_FIELD_NAME . '[这个条件是初始化条件，不用管]');
    }


    public function and($whereField,$valueData=[],$type=self::TYPE_IN,bool $isNot=false):self{
        $where=$whereField instanceof self?$whereField:static::make($whereField,$valueData,$type,$isNot);
        $this->ands[]=$where;
        return $this;
    }

    public function or($whereField,$valueData=[],$type=self::TYPE_IN,bool $isNot=false):self{
        $where=$whereField instanceof self?$whereField:static::make($whereField,$valueData,$type,$isNot);
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
            'RETURN_FALSE_FIELD_NAME'=>self::RETURN_FALSE_FIELD_NAME
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


    private function checkSelf($saveDatas,bool $isSourceData,BaseModel $info):bool{
        if($this->field->name()===self::RETURN_FALSE_FIELD_NAME){
            return count($this->ands)>0;
        }

        if(!$isSourceData){
            $isOldHave=!isset($saveDatas[$this->field->name()])&&isset($info[$this->field->name()]);
            if($this->field->required()){
                $field=clone $this->field;
                $field->required(false);
            }else{
                $field= $this->field;
            }
            $field->setSave($saveDatas,$info);
            $saveDatas[$this->field->name()]=$field->getSave();
            if($isOldHave&&$saveDatas[$this->field->name()]===$field->nullVal()){
                $saveDatas[$this->field->name()]=$info[$this->field->name()];
            }
        }
        if(!isset($saveDatas[$this->field->name()])){
            if(!$info||!isset($info[$this->field->name()])){
                return $this->isNot;
            }
            $val=$info[$this->field->name()];
        }else{
            $val=$saveDatas[$this->field->name()];
        }

        if(is_null($val)){
            return $this->isNot;
        }

        if(is_array($val)){
            throw new \think\Exception('配置错误');
        }

        return $this->checkVal($val)!==$this->isNot;
    }

    private function checkVal($val):bool{
        if($this->type===self::TYPE_IN){
            return in_array((string)$val, array_map(static fn($v) => (string)$v, $this->valueData), true);
        }
        if($this->type===self::TYPE_FIND_IN_SET){
            $vals=is_array($val)?$val:explode(',',$val);
            $vals=array_map('strval',$vals);
            return (bool)array_intersect($this->valueData,$vals);
        }
        if(is_null($this->valueData[0])){
            return bccomp($val,$this->valueData[1],6)!==1;
        }

        if(is_null($this->valueData[1])){
            return bccomp($val,$this->valueData[0],6)!==-1;
        }

        return bccomp($val,$this->valueData[1],6)!==1&&bccomp($val,$this->valueData[0],6)!==-1;
    }


    /**
     * 验证数据是否符合条件
     * @param array $saveDatas
     * @param bool $isSourceData 是否数据为源数据，未经过字段的setSave处理
     * @param BaseModel|null $info  原数据
     * @return bool
     * @throws \think\Exception
     */
    public function check(array $saveDatas,bool $isSourceData,BaseModel $info):bool{
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


    /**
     * @param Query|Model $query
     * @return Query|Model
     */
    private function getWhere($query){
        $name=$this->field->name();
        if($this->type===self::TYPE_IN){
            return $query->where($name,$this->isNot?'not in':'in',$this->valueData);
        }
        if($this->type===self::TYPE_FIND_IN_SET){
            if($this->isNot){
                $sqls=[];
                foreach ($this->valueData as $v){
                    $sqls[]='FIND_IN_SET("'.addslashes($v).'",'.$name.')';
                }
                if(count($sqls)>1){
                    return $query->whereRaw('NOT ('.implode(' OR ',$sqls).')');
                }
                return $query->whereRaw('NOT '.current($sqls));
            }
            if(count($this->valueData)>0){
                return $query->where(function (Query $query)use($name){
                    foreach ($this->valueData as $v){
                        $query->whereFindInSet($name,$v,'OR');
                    }
                });
            }
            return $query->whereFindInSet($name,current($this->valueData));
        }

        if(is_null($this->valueData[0])){
            return $query->where($name,$this->isNot?'>':'<=',$this->valueData[1]);
        }

        if(is_null($this->valueData[1])){
            return $query->where($name,$this->isNot?'<':'>=',$this->valueData[0]);
        }

        if($this->isNot){
            return $query->whereNotBetween($name,$this->valueData);
        }

        return $query->whereBetween($name,$this->valueData);
    }

    /**
     * 转换到数据库查询条件
     * @param Query|Model $query
     * @param bool $isOr  是否为 or 条件
     */
    public function toQuery(&$query,bool $isOr=false):void{
        $name=$this->field->name();
        $func=function ($query)use($name){
            $thisWhere=function ($query)use($name){
                $fields=$query->getTableFields();
                if($name!==self::RETURN_FALSE_FIELD_NAME){
                    if(in_array($name,$fields,true)){
                        $query=$this->getWhere($query);
                    }else{
                        if(!$this->isNot){
                            //已不满足条件，不再往下执行
                            return $query->where($query->getPk(),'FIELD-WHERE-NOT-FIELD');
                        }
                    }
                }
                foreach ($this->ands as $v){
                    $v->toQuery($query);
                }
                return $query;
            };
            if($this->ors){
                $query=$query->where($thisWhere);
                foreach ($this->ors as $v){
                    $v->toQuery($query,true);
                }
            }else{
                $query=$thisWhere($query);
            }
            return $query;
        };
        if($isOr){
            $query=$query->whereOr($func);
            return;
        }
        if($this->ors){
            $query=$query->where($func);
            return;
        }
        $query=$func($query);
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