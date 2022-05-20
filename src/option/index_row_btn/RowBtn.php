<?php

namespace tpScriptVueCurd\option\index_row_btn;

use tpScriptVueCurd\FieldCollection;

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

    public function toArray():array{
        $btn=parent::toArray();
        $modalFields=$this->modalFields?array_values($this->modalFields->rendGroup()->fieldToArrayPageType('edit')->toArray()):null;
        $btn['modalFields']=$modalFields;
        $btn['modalGroupFields']=$this->modalFields&&$this->modalFields->groupItems?FieldCollection::groupListByItems($modalFields):null;
        $btn['modalFieldsComponents']=$this->modalFields?$this->modalFields->getComponents('edit'):[];
        $btn['saveUrl']=$this->saveUrl;
        $btn['saveBtnTitle']=$this->saveBtnTitle;
        $btn['info']=$this->info;
        $btn['refreshList']=$this->refreshList;
        return $btn;
    }

}