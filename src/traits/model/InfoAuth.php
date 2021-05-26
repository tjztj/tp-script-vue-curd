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
     * @param FieldCollection $field
     * @param BaseModel|null $baseInfo
     * @param array|string[] $types
     * @return $this
     */
    public function rowSetAuth(FieldCollection $field,BaseModel $baseInfo=null,array $types=['show','edit','del','add']): self
    {
        $authCheck=new ModelInfoAuth();
        $this->authCheck($authCheck);

        $arr=$this->__auth??[];

        if(in_array('show', $types, true) &&isset($authCheck->show)&&$authCheck->show){
            $func=$authCheck->show;
            $arr['show']=(!isset($arr['show'])||$arr['show'])&&$func($field,$this,$baseInfo);
        }
        if(in_array('edit', $types, true) &&isset($authCheck->edit)&&$authCheck->edit){
            $func=$authCheck->edit;
            $arr['edit']=(!isset($arr['edit'])||$arr['edit'])&&$func($field,$this,$baseInfo);
        }
        if(in_array('del', $types, true) &&isset($authCheck->del)&&$authCheck->del){
            $func=$authCheck->del;
            $arr['del']=(!isset($arr['del'])||$arr['del'])&&$func($field,$this,$baseInfo);
        }
        if(in_array('add', $types, true) &&isset($authCheck->add)&&$authCheck->add){
            $func=$authCheck->add;
            $arr['add']=(!isset($arr['add'])||$arr['add'])&&$func($field,$this,$baseInfo);
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
     * @param BaseModel|null $baseInfo
     * @param string $type
     * @return bool
     */
    public function checkRowAuth(FieldCollection $field,?BaseModel $baseInfo,string $type): bool
    {
        $this->rowSetAuth($field,$baseInfo,[$type]);
        if(!isset($this->__auth)){
            return true;
        }
        if(!isset($this->__auth[$type])){
            return true;
        }

        return $this->__auth[$type];
    }

}