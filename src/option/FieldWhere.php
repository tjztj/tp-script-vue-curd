<?php


namespace tpScriptVueCurd\option;


use think\db\Query;
use think\Model;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\field\StringField;
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
    public const NOT='__NOT__';
    public const NOT_IN=self::NOT;
    public const NOT_BETWEEN='__NOT_BETWEEN__';
    public const NOT_FIND_IN_SET='__NOT_FIND_IN_SET__';




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
     * @param array|string|int|float $valueData
     * @param string $type
     * @param bool $isNot  是否非，反转
     * @throws \think\Exception
     */
    public function __construct(ModelField $field, $valueData, string $type=self::TYPE_IN, bool $isNot=false)
    {
        switch ($type){
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

    public static function make($field,$valueData=[],$type=self::TYPE_IN,bool $isNot=false):self{
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


    private function checkSelf($saveDatas,bool $isSourceData,?VueCurlModel $info):bool{
        if($this->field->name()===self::RETURN_FALSE_FIELD_NAME){
            return false;
        }

        if(!$isSourceData){
            $isOldHave=!isset($saveDatas[$this->field->name()])&&$info&&isset($info[$this->field->name()]);
            if($this->field->required()){
                $field=clone $this->field;
                $field->required(false);
            }else{
                $field= $this->field;
            }
            $field->setSave($saveDatas);
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
            $vals=is_array($val)?$val:explode(',',$val);
            $vals=array_map('strval',$vals);
            return (bool)array_intersect($this->valueData,$vals);
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
            return $query->whereFindInSet($name,current($this->valueData));;
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
                    $query=$v->toQuery($query);
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


    /**
     * 获取当前不包括and与or的where结构
     * @return string
     */
    private function getDump(bool $dumpIsHtml=false):string{
        $name=$this->field->name();
        if($dumpIsHtml){
            $name='<span style="color:#006d75;border-bottom: 1px solid #36cfc9">'.$name.'</span>';
        }
        if($this->type===self::TYPE_IN){
            if(count($this->valueData)>1){
                if($dumpIsHtml){
                    return $name.' '.($this->isNot?'<b style="color: #c41d7f">NOT IN</b>':'<b style="color: #c41d7f">IN</b>').' <b style="color: #c41d7f">("</b><span style="color: #8c8c8c">'.implode('</span><b style="color: #c41d7f">","</b><span style="color: #8c8c8c">',$this->valueData).'</span><b style="color: #c41d7f">")</span>';
                }
                return $name.' '.($this->isNot?'NOT IN':'IN').' ("'.implode('","',$this->valueData).'")';
            }
            if($dumpIsHtml){
                return $name.' '.($this->isNot?'<b style="color: #c41d7f"><></b>':'<b style="color: #c41d7f">=</b>').' <b style="color: #c41d7f">"</b><span style="color: #8c8c8c">'.current($this->valueData).'</span><b style="color: #c41d7f">"</b>';
            }
            return $name.' '.($this->isNot?'<>':'=').' "'.current($this->valueData).'"';

        }
        if($this->type===self::TYPE_FIND_IN_SET){
            if($this->isNot){
                $sqls=[];
                foreach ($this->valueData as $v){
                    if($dumpIsHtml){
                        $sqls[]='<b style="color: #c41d7f">FIND_IN_SET("</b><span style="color: #8c8c8c">'.addslashes($v).'</span><b style="color: #c41d7f">",</b>'.$name.'<b style="color: #c41d7f">)</b>';
                    }else{
                        $sqls[]='FIND_IN_SET("'.addslashes($v).'",'.$name.')';
                    }

                }
                if(count($sqls)>0){
                    if($dumpIsHtml){
                        return '<b style="color: #c41d7f">NOT (</b>'.implode(' <b style="color: #c41d7f">OR</b> ',$sqls).'<b style="color: #c41d7f">)</b>';
                    }
                    return 'NOT ('.implode(' OR ',$sqls).')';
                }
                if($dumpIsHtml){
                    return '<b style="color: #c41d7f">NOT</b> '.current($sqls);
                }
                return 'NOT '.current($sqls);

            }
            $sqls=[];
            foreach ($this->valueData as $v){
                if($dumpIsHtml){
                    $sqls[]='<b style="color: #c41d7f">FIND_IN_SET("</b><span style="color: #8c8c8c">'.addslashes($v).'</span><b style="color: #c41d7f">",</b>'.$name.'<b style="color: #c41d7f">)</b>';
                }else{
                    $sqls[]='FIND_IN_SET("'.addslashes($v).'",'.$name.')';
                }
            }
            if($dumpIsHtml){
                $sqlStr=implode(' <b style="color: #c41d7f">OR</b> ',$sqls);
            }else{
                $sqlStr=implode(' OR ',$sqls);
            }

            if(count($sqls)>1){
                if($dumpIsHtml){
                    return '<b style="color: #c41d7f">(</b>'.$sqlStr.'<b style="color: #c41d7f">)</b>';
                }
                return '('.$sqlStr.')';
            }
            return $sqlStr;
        }

        if(is_null($this->valueData[0])){
            if($dumpIsHtml){
                return $name.' '.($this->isNot?'<b style="color: #c41d7f">&gt;</b>':'<b style="color: #c41d7f">&lt;=</b>').'<b style="color: #c41d7f">"</b><span style="color: #8c8c8c">'.$this->valueData[1].'</span><b style="color: #c41d7f">"</b>';
            }
            return $name.' '.($this->isNot?'>':'<=').'"'.$this->valueData[1].'"';
        }

        if(is_null($this->valueData[1])){
            if($dumpIsHtml){
                return $name.' '.($this->isNot?'<b style="color: #c41d7f">&lt;</b>':'<b style="color: #c41d7f">&gt;=</b>').'<b style="color: #c41d7f">"</b><span style="color: #8c8c8c">'.$this->valueData[0].'</span><b style="color: #c41d7f">"</b>';
            }
            return $name.' '.($this->isNot?'<':'>=').'"'.$this->valueData[0].'"';
        }

        if($this->isNot){
            if($dumpIsHtml){
                return '<b style="color: #c41d7f">(</b>'.$name.' <b style="color: #c41d7f">&lt;"</b><span style="color: #8c8c8c">'.$this->valueData[0].'</span><b style="color: #c41d7f">" AND</b> '.$name.' <b style="color: #c41d7f">&gt; "</b><span style="color: #8c8c8c">'.$this->valueData[1].'</span><b style="color: #c41d7f">")</b>';
            }
            return '('.$name.' <"'.$this->valueData[0].'" AND '.$name.' > "'.$this->valueData[1].'")';
        }
        if($dumpIsHtml){
            return '<b style="color: #c41d7f">(</b>'.$name.' <b style="color: #c41d7f">&gt;="</b><span style="color: #8c8c8c">'.$this->valueData[0].'</span><b style="color: #c41d7f">" AND</b> '.$name.' <b style="color: #c41d7f">&lt;= "</b><span style="color: #8c8c8c">'.$this->valueData[1].'</span><b style="color: #c41d7f">")</b>';
        }
        return '('.$name.' >="'.$this->valueData[0].'" AND '.$name.' <= "'.$this->valueData[1].'")';
    }


    /**
     * 打印当前结构
     * @param bool $dumpIsHtml  使用HTML，阅读更方便
     */
    public function dump(bool $dumpIsHtml=false):void{
        $pdEm=2;
        $str=$this->dumpStr($dumpIsHtml,false,$pdEm);
        if($dumpIsHtml){
            $str='<div style="margin-left: -'.$pdEm.'em;">'.$str.'</div>';
        }
        echo $str;
    }

    /**
     * 打印当前where结构
     * @param bool $dumpIsHtml
     * @param bool $haveParent
     * @param int $pdEm
     * @return string
     */
    public function dumpStr(bool $dumpIsHtml=false,bool $haveParent=false,int $pdEm=2):string{
        $levelStyle=' style="padding-left: '.$pdEm.'em"';
        $kStyle='padding-left: '.$pdEm.'em;font-weight: bold;color: #237804;';
        $kStyle2n='padding-left: '.($pdEm*2).'em;font-weight: bold;color: #237804;';

        $leftK=$dumpIsHtml?'<div style="'.$kStyle.'">(</div>':'(';
        $rightK=$dumpIsHtml?'<div style="'.$kStyle.'">)</div>':')';
        $leftK2=$dumpIsHtml?'<div style="'.$kStyle2n.'">(</div>':'(';
        $rightK2=$dumpIsHtml?'<div style="'.$kStyle2n.'">)</div>':')';
        $andHtm='<div style="margin-left: '.($pdEm).'em;color: #d48806;background-color: #fffbe6;width: 3em;text-align: center;">AND</div>';
        $orHtm='<div style="margin-left: '.($pdEm).'em;color: #9254de;background-color: #f9f0ff;width: 2em;text-align:center;">OR</div>';
        $orHtm2='<div style="margin-left: '.($pdEm*2).'em;color: #9254de;background-color: #f9f0ff;width: 2em;text-align:center;">OR</div>';



        $ands=[];
        if($this->field->name()!==self::RETURN_FALSE_FIELD_NAME){
            $ands[]=$this->getDump($dumpIsHtml);
        }
        foreach ($this->ands as $v){
            $ands[]=$v->dumpStr($dumpIsHtml,count($this->ors)>0||(empty($v->ands)?count($v->ors)>0:!empty($v->ors)),$pdEm);
        }
        if($dumpIsHtml){
            $str='<div '.$levelStyle.' data-loc="ands">'.implode('</div>'.$andHtm.'<div data-loc="c-ands">',$ands).'</div>';
        }else{
            $str=implode(' AND ',$ands);
        }


        if(empty($this->ors)){
            if(count($ands)>1&&$haveParent){
                return ' '.$leftK.' <div '.$levelStyle.' data-loc="and1-p">'.$str.'</div> '.$rightK.' ';
            }
            return $str;
        }
        $ors=[];
        foreach ($this->ors as $v){
            $ors[]=$v->dumpStr($dumpIsHtml,false,$pdEm);
        }

        if($dumpIsHtml){
            $orStr='<div '.$levelStyle.' data-loc="ors">'.implode('</div>'.$orHtm.'<div data-loc="c-ors">',$ors).'</div>';
        }else{
            $orStr=implode(' OR ',$ors);
        }


        if(count($ands)>1){
            $str=' '.$leftK.' <div '.$levelStyle.' data-loc="and-gt1-have-or">'.$str.'</div> '.$rightK.' ';
        }

        if(count($ors)>0){
            if($haveParent){
                $orStr=' '.$leftK2.' '.$orStr.' '.$rightK2.' ';
            }else{
                $orStr=' '.$leftK.' '.$orStr.' '.$rightK.' ';
            }
        }
        if($haveParent){
            return ' '.$leftK.' '.$str.($dumpIsHtml?$orHtm2:' OR ').$orStr.' '.$rightK.' ';
        }
        return $str.($dumpIsHtml?$orHtm:' OR ').$orStr;
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