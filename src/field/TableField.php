<?php

namespace tpScriptVueCurd\field;


use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class TableField extends ListField
{

    /**
     * 如果设置为0，代表不启用分页
     * @var int
     */
    protected int $pageSize=0;

    /**
     * 是否线上操作栏
     * @var bool
     */
    protected bool $showAction=true;


    /**
     * @var array 自定义要额外引入的字段与url
     */
    protected array $componentUrlArr=[];

    /**
     * 每页显示数量
     * @param int|null $pageSize
     * @return TableField|int
     */
    public function pageSize(int $pageSize=null){
        return $this->doAttr('pageSize',$pageSize);
    }


    /**
     * 是否显示操作栏
     * @param bool|null $showAction
     * @return TableField|bool
     */
    public function showAction(bool $showAction=null){
        return $this->doAttr('showAction',$showAction);
    }


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tp-script-vue-curd-static.php?field/table/index.js'),
            new Show($type,'/tp-script-vue-curd-static.php?field/table/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/table/edit.js')
        );
    }


    /**
     * 设置自定义要额外引入的字段与url
     * @param array $componentUrlArr 自定义要额外引入的字段与url集合
     * @return $this
     */
    public function setComponentUrlArr(array $componentUrlArr):self{
        $this->componentUrlArr=$componentUrlArr;
        return $this;
    }

    /**
     * 获取自定义要额外引入的字段与url
     * @return array
     */
    public function getComponentUrlArr():array{
        return  $this->componentUrlArr;
    }


    public function toArray(): array
    {
        $arr=parent::toArray();

        $listColumns=array_values($this->fields->listShowItems()->toArray());
        $editFieldArr=array_values($this->fields->toArray());

        $showFields=clone $this->fields;
        $showFields=$showFields->filter(fn(ModelField $v)=>$v->showPage())->rendGroup();
        $showFieldArr=array_values($showFields->toArray());

        $componentUrl=[...$this->componentUrlArr];
        foreach ([$showFields->getComponents('show'),
                     $this->fields->getComponents('edit'),
                     $this->fields->getComponents('index')] as $v){
            foreach ($v as $val){
                $componentUrl[]=$val;
            }
        }



        $arr['pageData']=[
            'listColumns'=>$listColumns,
            'editGroupColumns'=>$this->fields->groupItems? FieldCollection::groupListByItems($listColumns):null,//不管显示是不是一个组，只要groupItems有，列表就分组
            'editGroupFields'=>$this->fields->groupItems?FieldCollection::groupListByItems($editFieldArr):null,
            'showFields'=>$showFieldArr,
            'showGroupFields'=>$showFields->groupItems?FieldCollection::groupListByItems($showFieldArr):null,
            'componentUrl'=>$componentUrl
        ];


        return $arr;
    }


    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeJson();
    }
}
