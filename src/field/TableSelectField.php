<?php

namespace tpScriptVueCurd\field;

use think\Exception;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\SelectFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class TableSelectField extends ModelField
{
    protected string $defaultFilterClass=SelectFilter::class;
    /**
     * 如果设置为0，代表不启用分页
     * @var int
     */
    protected int $pageSize=5;//每页显示数量，为0不分页
    protected array $fields=[];//字段集合
    protected string $url='';
    protected bool $multiple=false;//是否可多选




    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tpscriptvuecurd/field/table_select/index.js'),
            new Show($type,'/tpscriptvuecurd/field/table_select/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/table_select/edit.js')
        );
    }


    /**
     * 字段集合 表格显示的字段 如：['name'=>'标题','address'=>'住址']
     * @param array|null $fields
     * @return $this|array
     * @throws Exception
     */
    public function fields(array $fields=null){
        if(is_null($fields)){
            if(empty($this->fields)){
                throw new \think\Exception('未设置字段'.$this->name().'的fields');
            }
            return $this->fields;
        }
        $this->fields=$fields;
        $this->fieldPushAttrByWhere('fields',$fields);
        return $this;
    }

    /**
     * 设置远程url
     * @throws Exception
     */
    public function url(string $url=null){
        if(is_null($url)){
            if(!isset($this->url)){
                throw new \think\Exception('未设置字段'.$this->name().'的url');
            }
            return $this->url;
        }
        $this->url=$url;
        $this->fieldPushAttrByWhere('url',$url);
        return $this;
    }


    /**
     * 是否可多选
     * @param bool|null $multiple
     * @return $this|bool
     */
    public function multiple(bool $multiple=null){
        return $this->doAttr('multiple',$multiple);
    }

    /**
     * 每页显示数量，为0不分页
     * @param int|null $pageSize
     * @return TableSelectField
     */
    public function pageSize(int $pageSize=null){
        return $this->doAttr('pageSize',$pageSize);
    }


    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {

    }

    public function getGenerateColumnConfig(GenerateColumnOption $option): void
    {
        $option->setTypeVarchar();
    }
}