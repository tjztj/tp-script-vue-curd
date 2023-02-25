<?php

namespace tpScriptVueCurd\traits\controller;

use tpScriptVueCurd\field\TreeSelectField;

trait TreeIndex
{

    /**
     * @var string 父级的id的字段
     */
    public string $treePidField='';

    /**
     * @var string 指定树形结构的列名
     */
    public string $childrenColumnName='children';

    /**
     * @var int 展示树形数据时，每层缩进的宽度，以 px 为单位
     */
    public int $indentSize=15;

    /**
     * @var bool 是否展开所有行
     */
    public bool $expandAllRows=false;

    /**
     * 普通列表数据转换为树形列表
     * @param array $list
     * @return array
     */
    public function listToTree(array $list):array{
        if(empty($list)){
            return [];
        }

        $curRow=current($list);
        $pidField=isset($curRow['_Original_'.$this->treePidField])?'_Original_'.$this->treePidField:$this->treePidField;
        return TreeSelectField::listToTree($list,'id',$pidField,$this->childrenColumnName);
    }
}