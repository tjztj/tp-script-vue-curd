<?php

namespace tpScriptVueCurd\option;

use think\db\Query;
use tpScriptVueCurd\field\TreeSelectField;
use tpScriptVueCurd\option\index_row_btn\OpenBtn;

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


    private $listCall;


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


    /**
     * 添加按钮配置
     * @var OpenBtn|null
     */
    public ?OpenBtn $addBtn=null;
    /**
     * 编辑按钮配置
     * @var OpenBtn|null
     */
    public ?OpenBtn $editBtn=null;
    /**
     * 删除的url
     * @var string
     */
    public string $rmUrl='';

    private array $tree=[];
    private array $list=[];


    /**
     * $listCall 需返回
     * [
     *  ['value'=>1,'pvalue'=>0,'title'=>'父数据'],
     *  ['value'=>2,'pvalue'=>1,'title'=>'子数据'],
     *  ['value'=>2,'pvalue'=>1,'title'=>'子数据']
     * ]
     */
    public function __construct(callable $listCall)
    {
        $this->setListCallable($listCall);
    }

    /**
     * $listCall 需返回
     * [
     *  ['value'=>1,'pvalue'=>0,'title'=>'父数据'],
     *  ['value'=>2,'pvalue'=>1,'title'=>'子数据'],
     *  ['value'=>2,'pvalue'=>1,'title'=>'子数据']
     * ]
     */
    public function setListCallable(callable $listCall):void
    {
        $this->listCall=$listCall;
    }



    public function getTree():array{
        if(empty($this->tree)){
            $this->tree=isset($this->listCall)?TreeSelectField::listToTree(($this->listCall)(),'value','pvalue'):[];
        }
        return $this->tree;
    }


    /**
     * 获取列表数据
     * @return void
     */
    public function getList():array{
        if(empty($this->list)){
            $this->list=TreeSelectField::treeToList($this->getTree(),'value');
        }
        return $this->list;
    }




    public function toArray():array{
        return [
            'show'=>$this->show,
            'title'=>$this->title,
            'list'=>$this->getTree(),
            'width'=>$this->width,
            'paramName'=>$this->paramName,
            'defaultExpandAll'=>$this->defaultExpandAll,
            'addBtn'=>$this->addBtn?$this->addBtn->toArray():null,
            'editBtn'=>$this->editBtn?$this->editBtn->toArray():null,
            'rmUrl'=>$this->rmUrl
        ];
    }
}