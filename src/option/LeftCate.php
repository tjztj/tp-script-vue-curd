<?php

namespace tpScriptVueCurd\option;

use think\db\Query;
use tpScriptVueCurd\field\TreeSelect;

class LeftCate
{
    /**
     * 是否显示左侧分组
     * @var bool
     */
    public bool $show=false;

    /**
     * 标题
     * @var string
     */
    public string $title='';



    /**
     * 列表，格式
     * [
     *  ['value'=>1,'pvalue'=>0,'title'=>'父数据'],
     *  ['value'=>2,'pvalue'=>1,'title'=>'子数据'],
     *  ['value'=>2,'pvalue'=>1,'title'=>'子数据']
     * ]
     * @var array
     */
    public array $list=[];


    /**
     * 宽度
     * @var string
     */
    public string $width='18vw';


    /**
     * list中的value将会通过参数left_cate_id发送给控制器
     * @var string
     */
    public string $paramName='left_cate_id';


    /**
     * 是否默认展开所有节点
     * @var bool
     */
    public bool $defaultExpandAll=true;

    /**
     * 右侧列表的查询条件，根据参数left_cate_id来处理
     * @var array|string|callable
     */
    public $where=[];



    public function toArray():array{
        return [
            'show'=>$this->show,
            'title'=>$this->title,
            'list'=>TreeSelect::listToTree($this->list,'value','pvalue'),
            'width'=>$this->width,
            'paramName'=>$this->paramName,
            'defaultExpandAll'=>$this->defaultExpandAll,
        ];
    }
}