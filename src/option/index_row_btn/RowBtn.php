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

    public function toArray():array{
        $modalFields=$this->modalFields?array_values($this->modalFields->rendGroup()->fieldToArrayPageType('edit')->toArray()):null;
        return [
            ...parent::toArray(),
            'modalFields'=>$modalFields,
            'modalGroupFields'=>$this->modalFields&&$this->modalFields->groupItems?FieldCollection::groupListByItems($modalFields):null,
            'modalFieldsComponents'=>$this->modalFields?$this->modalFields->getComponents('edit'):[],

            'saveUrl'=>$this->saveUrl,
            'saveBtnTitle'=>$this->saveBtnTitle,
            'info'=>$this->info,
        ];
    }

}