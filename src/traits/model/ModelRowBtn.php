<?php

namespace tpScriptVueCurd\traits\model;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\option\index_row_btn\RowBtn;

trait ModelRowBtn
{

    /**
     * 列表按钮组左侧
     * @param FieldCollection $fields
     * @param BaseModel|null $parentInfo
     * @param \think\model\Collection $list
     * @return RowBtn[]
     */
    public function getListRowBeforeBtns(FieldCollection $fields,?BaseModel $parentInfo,\think\model\Collection $list): array
    {
        //$info==$this;
        return [];
    }


    /**
     * 列表按钮组右侧
     * @param FieldCollection $fields
     * @param BaseModel|null $parentInfo
     * @param \think\model\Collection $list
     * @return RowBtn[]
     */
    public function getListRowAfterBtns(FieldCollection $fields,?BaseModel $parentInfo,\think\model\Collection $list): array
    {
        //$info==$this;
        return [];
    }






}