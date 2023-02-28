<?php

namespace tpScriptVueCurd\traits\field;

trait ListEdit
{
    /**
     * 是否可在列表中编辑当前字段的值
     * @var array|string[]
     */
    protected array $listEdit=[
        'saveUrl'=>'',
        'refreshPage'=>'',//table,row,''空字符串不刷新
    ];


    /**
     * 是否可在列表中编辑当前字段的值
     * @param string|null $saveUrl
     * @param string $refreshPage
     * @return $this|array|string[]
     */
    public function listEdit(string $saveUrl=null,string $refreshPage=''){
        if(is_null($saveUrl)){
            return $this->listEdit;
        }
        $this->listEdit['saveUrl']=$saveUrl;
        $this->listEdit['refreshPage']=$refreshPage;
        return $this;
    }

}