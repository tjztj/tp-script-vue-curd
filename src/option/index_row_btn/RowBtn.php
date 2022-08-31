<?php

namespace tpScriptVueCurd\option\index_row_btn;

use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;

class RowBtn extends Btn
{

    public ?FieldCollection $modalFields=null;

    public string $saveUrl='';
    public string $saveBtnTitle='提交';

    /**
     * 可以设置默认值
     * @var array|null
     */
    public ?array $info=[];


    public bool $refreshList=false;//操作成功后刷新列表
    public bool $refreshPage=false;//操作成功后刷新页面

    public function toArray():array{
        $btn=parent::toArray();
        $modalFields=$this->modalFields?array_values($this->modalFields->rendGroup()->fieldToArrayPageType('edit')->toArray()):null;
        $modalGroupFields=$this->modalFields&&$this->modalFields->groupItems?FieldCollection::groupListByItems($modalFields):null;

        $groupGrids=[];
        if($this->modalFields){
            $this->modalFields->each(function (ModelField $field){
                $func=$field->getEditGridBy();
                $func&&$field->grid($func($this->info?:[],null,$field));
            });
            foreach ($modalGroupFields?:[''=>$this->modalFields->all()] as $k=>$v){
                $func=$this->modalFields->getEditGridBy();
                $groupGrids[$k]=$func?$func($this->info?:[],null,$v,$k):null;
            }
        }



        $btn['modalFields']=$modalFields;
        $btn['modalGroupFields']=$modalGroupFields;
        $btn['modalGroupGrids']=$groupGrids;
        $btn['modalFieldsComponents']=$this->modalFields?$this->modalFields->getComponents('edit'):[];
        $btn['saveUrl']=$this->saveUrl;
        $btn['saveBtnTitle']=$this->saveBtnTitle;
        $btn['info']=$this->info;
        $btn['refreshList']=$this->refreshList;
        $btn['refreshPage']=$this->refreshPage;
        return $btn;
    }

}