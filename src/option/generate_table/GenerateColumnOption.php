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

    /**
     * @var string|null 默认值
     */
    protected ?string $default;


    /**
     * @var string 字段备注
     */
    protected string $comment = '';


    public function __construct(string $name)
    {
        if(!self::checkName($name)){
            throw new \think\Exception('字段名称不符合规范（需小写字母开头，可包含a到z、_、0到9）');
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
     * @param string $type MODIFY|ADD
     * @param string $beforeField 前面的字段
     * @return string
     * @throws \think\Exception
     */
    public function getSql(string $type='',string $beforeField=''):string{
        $type=strtoupper($type);
        if($type!=='MODIFY'&&$type!=='ADD'){
            throw new \think\Exception('GenerateColumnOption的getSql中$type不符合要求（MODIFY|ADD）');
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

        return $type." COLUMN `$this->name` ".$this->getTypeStr()." NOT NULL $default COMMENT '$comment' $beforeField";
    }


    public function getTypeStr():string{
        $typeStr=$this->type;
        if($this->length){
            $typeStr.='('.$this->length;
            if($this->type==='float'||$this->type==='decimal'){
                $typeStr.=',2';
            }
            $typeStr.=')';
        }

        return $typeStr;
    }


    public function getDefaultStr()
    {
        if(is_null($this->default)){
            return null;
        }else{
            if($this->type==='varchar'){
                $default=(string)$this->default;
            }else if(strpos($this->default,'.')){
                $default=(float)$this->default;
            }else{
                $default=(int)$this->default;
            }
            return $default;
        }
    }

    public function checkIsChange(array $field):bool{
        if($field['comment']!==$this->comment){
            return true;
        }
        $def=$this->getDefaultStr();
        if((is_null($def)||is_null($field['default']))&&$field['default']!==(string)$def){
            return true;
        }
        if(strtolower($this->getTypeStr())!==$field['type']){
            return true;
        }
        return false;
    }

}
