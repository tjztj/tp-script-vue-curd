<?php


namespace tpScriptVueCurd\traits\model;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\option\ModelInfoAuth;

trait InfoAuth
{


    /**
     * 子类继承重写
     * @param ModelInfoAuth $authCheck
     * @return void
     */
    protected function authCheck(ModelInfoAuth $authCheck):void{

    }



    /**
     * 为数据设置权限判断结果
     * @param FieldCollection $fields
     * @param BaseModel|null $parentInfo
     * @param array|string[] $types
     * @return $this
     */
    public function rowSetAuth(FieldCollection $fields,BaseModel $parentInfo=null,array $types=['show','edit','del','add']): self
    {
        $authCheck=new ModelInfoAuth();
        $this->authCheck($authCheck);

        $arr=$this->__auth??[];

        foreach (['show','edit','del','add'] as $v) {
            if (!in_array($v, $types, true)) {
                continue;
            }
            if (!isset($authCheck->$v)) {
                continue;
            }
            if (isset($arr[$v]) && $arr[$v] === false) {
                continue;
            }
            if (is_callable($authCheck->$v)) {
                $func = $authCheck->$v;
                $arr[$v] = $func($fields, $this, $parentInfo);
            } else if (is_bool($authCheck->$v)) {
                $arr[$v] = $authCheck->$v;
            }
        }


        if(empty($arr)){
            unset($this->__auth);
        }else{
            $this->__auth=$arr;
        }
        return $this;
    }


    /**
     * 对某一条数据验证是否可操作
     * @param FieldCollection $field
     * @param BaseModel|null $parentInfo
     * @param string $type
     * @return bool
     */
    public function checkRowAuth(FieldCollection $field,?BaseModel $parentInfo,string $type): bool
    {
        $this->rowSetAuth($field,$parentInfo,[$type]);
        if(!isset($this->__auth)){
            return true;
        }
        if(!isset($this->__auth[$type])){
            return true;
        }

        return $this->__auth[$type];
    }

}