<?php

namespace tpScriptVueCurd\option\index_row_btn;

use tpScriptVueCurd\FieldCollection;

class RowBtn
{
    public string $btnTitle='';
    public string $btnColor='';

    public string $modalW='45vw';
    public string $modalH='100vh';
    public string $modalTitle='';
    public ?FieldCollection $modalTitleFields=null;

    public string $saveUrl='';
    public string $saveBtnTitle='提交';

    /**
     * 可以设置默认值
     * @var array|null
     */
    public ?array $info=[];

    public function toArray():array{
        $modalTitleFields=$this->modalTitleFields?array_values($this->modalTitleFields->rendGroup()->fieldToArrayPageType('edit')->toArray()):null;
        return [
            'btnTitle'=>$this->btnTitle,
            'btnColor'=>$this->btnColor,
            
            'modalW'=>$this->modalW,
            'modalH'=>$this->modalH,
            'modalTitle'=>$this->modalTitle?:$this->btnTitle,
            'modalTitleFields'=>$modalTitleFields,
            'modalTitleGroupFields'=>$this->modalTitleFields&&$this->modalTitleFields->groupItems?FieldCollection::groupListByItems($modalTitleFields):null,
            'modalTitleFieldsComponents'=>$this->modalTitleFields?$this->modalTitleFields->getComponents('edit'):[],

            'saveUrl'=>$this->saveUrl,
            'saveBtnTitle'=>$this->saveBtnTitle,
            'info'=>$this->info,
        ];
    }

}