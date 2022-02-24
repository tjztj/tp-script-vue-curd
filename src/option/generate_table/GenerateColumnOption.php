<?php

namespace tpScriptVueCurd\option\generate_table;

use tpScriptVueCurd\option\generate_table\traits\ColumnGetterSetter;
use tpScriptVueCurd\option\generate_table\traits\ColumnSetTypes;

class GenerateColumnOption
{
    use ColumnSetTypes,ColumnGetterSetter;

    /**
     * @var string 字段名称
     */
    protected string $name;

    /**
     * @var string 字段类型
     */
    protected string $type;


    /**
     * @var int|null 字段长度
     */
    protected ?int $length = null;


    protected int $precision=0;

    /**
     * @var string|null 默认值
     */
    protected ?string $default;


    /**
     * @var string 字段备注
     */
    protected string $comment = '';

    /**
     * @var string 编码
     */
    protected string $chart='';


    public function __construct(string $name)
    {
        $name=str_replace('__CLONE__','',$name);
        if(!self::checkName($name)){
            throw new \think\Exception('字段名称['.$name.']不符合规范（需小写字母开头，可包含a到z、_、0到9）');
        }
        $this->name=$name;
    }


    /**
     * 判断字段名称是否符合标准
     * @param string $name
     * @return bool
     */
    public static function checkName(string $name):bool{
        return (bool)preg_match('/^[a-z][a-z_0-9]*$/', $name);
    }

    /**
     * 获取执行的sql
     * @param string $type MODIFY|ADD|''
     * @param string $beforeField 前面的字段
     * @return string
     * @throws \think\Exception
     */
    public function getSql(string $type='',string $beforeField=''):string{
        $beforeField=str_replace('__CLONE__','',$beforeField);

        $type=strtoupper($type);
        if($type!=='MODIFY'&&$type!=='ADD'&&$type!==''){
            throw new \think\Exception('GenerateColumnOption的getSql中$type不符合要求（MODIFY|ADD|\'\'）');
        }

        $comment=addslashes($this->comment);

        $default=$this->getDefaultStr();
        if(is_null($default)){
            $default='';
        }else{
            if(is_string($default)){
                $default="'".addslashes($default)."'";
            }
            $default='DEFAULT '.$default;
        }


        if($beforeField){
            if(!self::checkName($beforeField)){
                throw new \think\Exception('GenerateColumnOption的getSql中$beforeField不符合要求（需小写字母开头，可包含a到z、_、0到9）');
            }
            $beforeField='AFTER `'.$beforeField.'`';
        }

        if($type){
            $type.=' COLUMN ';
        }

        return $type."`$this->name` ".$this->getTypeStr()." ".$this->getChart()." NOT NULL $default COMMENT '$comment' $beforeField";
    }


    /**
     * 获取类型处理的结果
     * @return string
     */
    public function getTypeStr():string{
        $typeStr=$this->type;
        if($this->length){
            $typeStr.='('.$this->length;
            if($this->type==='float'||$this->type==='decimal'){
                $typeStr.=','.$this->precision;
            }
            $typeStr.=')';
        }

        return $typeStr;
    }


    /**
     * 获取默认值处理的结果
     * @return float|int|string|null
     */
    public function getDefaultStr()
    {
        if(!isset($this->default)||is_null($this->default)){
            return null;
        }

        if($this->type==='varchar'){
            $default=(string)$this->default;
        }else if(strpos($this->default,'.')){
            $default=(float)$this->default;
        }else{
            $default=(int)$this->default;
        }
        return $default;
    }

    /**
     * 判断字段是否已经变更
     * @param array $field
     * @return bool
     */
    public function checkIsChange(array $field):bool{
        if($field['comment']!==$this->comment){
            return true;
        }
        $def=$this->getDefaultStr();
        if((is_null($def)&&!is_null($field['default']))&&$field['default']!==(string)$def){
            return true;
        }
        if(strtolower($this->getTypeStr())!==$field['type']){
            return true;
        }
        return false;
    }


    /**
     * 根据老的字段类型，改变当前的字段类型，因为有些字段类型的修改有影响
     * @param array $field
     * @throws \think\Exception
     */
    public function changeFieldTypeByOldField(array $field):void{
        $typeStr=$this->getTypeStr();
        if(strtolower($this->getTypeStr())===$field['type']){
            //如果字段的类型没有改变，不往下执行
            return;
        }

        $msg='系统不会在数据库表中为您改变这个字段的类型，请您自行到数据库表中更改字段的类型为'.$typeStr.'，或者配置字段不自动更改表：$field->generateColumn(false)。';
        $err='您在模型字段配置中将字段'.$this->name.'的类型由'.$field['type'].'改为了'.$typeStr.'，可能会造成数据的丢失。所以'.$msg;
        $lenErr='您在模型字段配置中将字段'.$this->name.'的类型由'.$field['type'].'改为了'.$typeStr.'，字段的长度变短，可能会造成数据的丢失。所以'.$msg;


        if($this->type!=='longtext'&&$field['type']==='longtext'){
            throw new \think\Exception($lenErr);
        }

        if(preg_match('/^([a-z]+)\((\d+)(?:,(\d+))?\)$/',$field['type'],$matches)){
            $oldType=$matches[1];
            $oldLen=$matches[2];
            $oldPlaces=$matches[3]??null;//小数位数
        }else{
            $oldType=$field['type'];
            $oldLen=null;
            $oldPlaces=null;//小数位数
        }

        switch ($this->type){
            case 'longtext':
                //这个因为字段长度很长，所以当字段类型改变为 longtext 时，不会丢失数据
                break;
            case 'text':
                //因为老的字段不是 longtext (上面那个判断过了)。所以如果新的字段的类型是 text ，那么数据不会丢失
                break;
            case 'int':
                //int只能由小变大，其他任何类型不能变为int
                if($oldType==='int'){
                    if($oldLen>$this->length){
                        //如果新设置的长度比，老的短，那么不改变长度
                        $this->length=$oldLen;
                    }
                }else{
                    //以前是其他类型，不允许改变为int
                    throw new \think\Exception($err);
                }
                break;
            case 'bigint':
                //bigint可改为int，除此外，不允许修改
                if($oldType==='int'||$oldType==='bigint'){
                    if($oldLen>$this->length){
                        //如果新设置的长度比，老的短，那么不改变长度
                        $this->length=$oldLen;
                    }
                }else{
                    //以前是其他类型，不允许改变为int
                    throw new \think\Exception($err);
                }
                break;
            case 'varchar':
                //除了text、longtext、json，其他的都可以改变为varchar
                if(empty($oldLen)||$oldType==='text'||$oldType==='longtext'||$oldType==='json'){
                    throw new \think\Exception($lenErr);
                }
                if($oldType==='decimal'||$oldType==='float'){
                    $oldLen+=$oldPlaces?:0;
                }
                if($oldLen>$this->length){
                    //如果新设置的长度比，老的短，那么不改变长度
                    $this->length=$oldLen;
                }
                break;
            case 'decimal':
            case 'float':
                //这两个字段处理方式一样
                if($oldType===$this->type){
                    if($oldPlaces&&$oldPlaces>$this->precision){
                        throw new \think\Exception($lenErr);
                    }
                    if($oldLen>$this->length){
                        //如果新设置的长度比，老的短，那么不改变长度
                        $this->length=$oldLen;
                    }
                }else if($oldType==='int'||$oldType==='bigint'){
                    if(($this->length-$oldLen)<$this->precision){
                        $this->length=$oldLen+$this->precision;
                    }
                }else{
                    throw new \think\Exception($err);
                }
                break;
            case 'json':
                //任何字段不能转为json
                if($oldType!==$this->type){
                    throw new \think\Exception('您在模型字段配置中将字段'.$this->name.'的类型由'.$field['type'].'改为了'.$typeStr.'，部分数据可能不是正确的json格式可能会出现无法设置成功。所以'.$msg);
                }
                break;
        }
    }

}
