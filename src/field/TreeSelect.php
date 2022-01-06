<?php

namespace tpScriptVueCurd\field;

use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\CascaderFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\ModelFilter;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class TreeSelect extends ModelField
{

    protected string $defaultFilterClass=CascaderFilter::class;

    protected bool $canCheckParent=false;//是否可选中父级
    protected bool $multiple=false;//是否多选
    protected int $dropdownMaxHeight=300;//弹出框最高高度
    protected bool $treeCheckStrictly=false;//多选的时候才会有效，checkable 状态下节点选择完全受控（父子节点选中状态不再关联）
    protected string $showCheckedStrategy='SHOW_ALL';//SHOW_CHILD、SHOW_PARENT、SHOW_ALL。定义选中项回填的方式。SHOW_ALL: 显示所有选中节点(包括父节点). SHOW_PARENT: 只显示父节点(当父节点下所有子节点都选中时). SHOW_CHILD只显示子节点.
    protected array $items=[];




    /**
     * 父字段名
     * @param bool|null $canCheckParent
     * @return $this|string
     */
    public function canCheckParent(bool $canCheckParent = null)
    {
        return $this->doAttr('canCheckParent', $canCheckParent);
    }


    /**
     * 是否可多选
     * @param bool $multiple
     * @return $this|bool
     */
    public function multiple(bool $multiple=null){
        return $this->doAttr('multiple',$multiple);
    }


    /**
     * 弹出框最高高度
     * @param int|null $dropdownMaxHeight
     * @return TreeSelect
     */
    public function dropdownMaxHeight(int $dropdownMaxHeight = null)
    {
        return $this->doAttr('dropdownMaxHeight', $dropdownMaxHeight);
    }


    /**
     * 多选的时候才会有效，checkable 状态下节点选择完全受控（父子节点选中状态不再关联）
     * @param bool|null $treeCheckStrictly
     * @return TreeSelect
     */
    public function treeCheckStrictly(bool $treeCheckStrictly=null){
        return $this->doAttr('treeCheckStrictly',$treeCheckStrictly);
    }

    /**
     * 定义选中项回填的方式。SHOW_ALL: 显示所有选中节点(包括父节点). SHOW_PARENT: 只显示父节点(当父节点下所有子节点都选中时). 默认只显示子节点.
     * @param string|null $showCheckedStrategy
     * @return TreeSelect
     */
    public function showCheckedStrategy(string $showCheckedStrategy=null){
        return $this->doAttr('showCheckedStrategy',$showCheckedStrategy);
    }


    public function items(array $items=null){
        if(is_null($items)){
            return $this->items;
        }
        if(empty($items)){
            $this->items=[];
            return $this;
        }
        foreach ($items as $k=>$v){
            $v['value']=(string)$v['value'];
            $v['pvalue']=(string)$v['pvalue'];
            $items[$k]['value']=$v['value'];
            $items[$k]['pvalue']=$v['pvalue'];
            isset($v['key'])||$items[$k]['key']=$v['value'];
        }

        $this->items=self::listToTree($items,'value','pvalue');
        if(empty($this->items)){
            throw new \think\Exception($this->name.'设置的items格式错误');
        }
        $this->fieldPushAttrByWhere('items',$this->items);
        return $this;
    }

    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        if(!isset($data[$this->name()])){
            $this->defaultCheckRequired('');
            return $this;
        }
        if(!isset($this->lists)){
            //为了防止导入的时候占用太多内存
            $this->lists=self::treeToList($this->items(),'value','children');
        }
        $list=$this->lists;

        if($this->multiple()){
            $vals=is_array($data[$this->name()])?$data[$this->name()]:explode(',',$data[$this->name()]);
            $saveVals=[];
            foreach ($vals as $v){
                $v=trim($v);
                if(!isset($list[$v])){
                    throw new \think\Exception('值'.$v.'不在可选中');
                }
                $saveVals[]=$v;
            }
            $val=implode(',',$saveVals);
        }else{
            $val=is_array($data[$this->name()])?(string)current($data[$this->name()]):$data[$this->name()];
        }
        $this->defaultCheckRequired($val);
        $this->save=$val;
        return $this;
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->width=22;
        $excelFieldTpl->wrapText = true;
        $excelFieldTpl->explain='填入：父级-子级（以“-”分开）';
        if($this->multiple()){
            $excelFieldTpl->explain.='多个用“|”分隔';
        }



        if(!isset($this->lists)){
            //为了防止导入的时候占用太多内存
            $this->lists=self::treeToList($this->items(),'value','children');
        }
        foreach ($this->lists as $v){
            $keys=[];
            foreach ($v['parents'] as $val){
                if(!isset($this->lists[$val])){
                    continue;
                }
                $keys[]=$this->lists[$val]['title'];
            }
            $keys[]=$v['title'];
            $excelFieldTpl->explain.="\n". implode('-',$keys);
        }

    }

    public function excelSaveDoData(array &$save): void
    {
        parent::excelSaveDoData($save);
        if(!isset($save[$this->name()])){
            return;
        }
        if($this->multiple()){
            $values=explode('|',$save[$this->name()]);
        }else{
            $values=[$save[$this->name()]];
        }
        if(!isset($this->lists)){
            //为了防止导入的时候占用太多内存
            $this->lists=self::treeToList($this->items(),'value','children');
        }
        $list=$this->lists;
        $items=[];
        foreach ($list as $v){
            $keys=[];
            foreach ($v['parents'] as $val){
                if(!isset($list[$val])){
                    continue;
                }
                $keys[]=$list[$val]['title'];
            }
            $keys[]=$v['title'];
            $items[implode('-',$keys)]=$v['value'];
        }


        $newValues=[];
        foreach ($values as $v){
            $v=trim($v);
            if($v===''){
                continue;
            }
            if(!isset($items[$v])){
                throw new \think\Exception('未找到可选值'.$v);
            }
            $newValues[]=$items[$v];
        }
        $save[$this->name()]=implode(',',$newValues);
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tp-script-vue-curd-static.php?field/tree_select/index.js'),
            new Show($type,'/tp-script-vue-curd-static.php?field/tree_select/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/tree_select/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        if($this->multiple()){
            $option->setTypeVarchar(500);
        }else{
            $option->setTypeVarchar(80);
        }
    }

    /**
     * 数据列表转换成树
     *
     * @param  array   $dataArr   数据列表
     * @param  string  $pkName    主键
     * @param  string  $pIdName   父节点名称
     * @param  string  $childName 子节点名称
     *
     * @return array  转换后的树
     */
    public static function listToTree(array $dataArr,string $pkName = 'id',string $pIdName = 'pid',string $childName = 'children'): array
    {
        $data = [];
        foreach ($dataArr as $row){
            $data[$row[$pkName]]=$row;
        }
        foreach($dataArr as $row) {
            $data[$row[$pIdName]]['children'][$row[$pkName]] = & $data[$row[$pkName]];
        }

        $tree=[];
        foreach ($data as $v){
            if(!isset($v[$pIdName])||!isset($data[$v[$pIdName]])){
                $tree[]=$v;
            }
        }
        if(count($tree)===1){
            $first=current($tree);
            if(isset($first[$childName])){
                $tree=$first[$childName];
            }
        }
        $setKey=function($arr)use(&$setKey,$childName){
            foreach ($arr as $k=>$v){
                if(isset($v[$childName])){
                    $arr[$k][$childName]=$setKey($v[$childName]);
                }
            }
            return array_values($arr);
        };

        return $setKey($tree);
    }


    public static function treeToList(array $tree,string $pkName,string $childName = 'children',$parents=[]):array
    {
        $list = [];
        foreach ($tree as $val) {
            $val['parents']=$parents;
            $list[$val[$pkName]]=$val;
            $list[$val[$pkName]]['childVals']=[];//所有子的ID
            $list[$val[$pkName]]['childLastVals']=[];//所有子最底层，的ID
            if (isset($val[$childName])) {
                foreach (self::treeToList($val[$childName],$pkName,$childName,[...$parents,$val[$pkName]]) as $v){
                    $list[$v[$pkName]]=$v;
                    $list[$val[$pkName]]['childVals'][]=$v[$pkName];
                    if(!isset($v[$childName])){
                        $list[$val[$pkName]]['childLastVals'][]=$v[$pkName];
                    }
                }
            }

        }
        return $list;
    }

    /**
     * Excel 模版中的下拉选项
     * @return array
     */
    public function excelSelectItems()
    {
        if($this->multiple())
            return [];
        if(!isset($this->lists)){
            //为了防止导入的时候占用太多内存
            $this->lists=self::treeToList($this->items(),'value','children');
        }
        $values=[];
        foreach ($this->lists as $v){
            $keys=[];
            foreach ($v['parents'] as $val){
                if(!isset($this->lists[$val])){
                    continue;
                }
                $keys[]=$this->lists[$val]['title'];
            }
            $keys[]=$v['title'];
            $values[]=implode('-',$keys);
        }
        return $values;
    }
    /**
     * 导出到excel时数据处理
     * @param array $data
     * @return string
     */
    public function getExportText(array $data): string
    {
        if(!isset($data[$this->name()])){
            return '';
        }
        if($this->multiple()){
            $values=explode(',',$data[$this->name()]);
        }else{
            $values=[$data[$this->name()]];
        }

        if(!isset($this->lists)){
            //为了防止导入的时候占用太多内存
            $this->lists=self::treeToList($this->items(),'value','children');
        }
        $list=$this->lists;
        $items=[];
        foreach ($list as $v){
            $findKey=array_search($v['value'],$values);
            if($findKey===false){
                continue;
            }
            $keys=[];
            foreach ($v['parents'] as $val){
                if(!isset($list[$val])){
                    continue;
                }
                $keys[]=$list[$val]['title'];
            }
            $keys[]=$v['title'];
            $items[$v['value']]=implode('-',$keys);
            unset($values[$findKey]);
            if(empty($values)){
                break;
            }
        }
        return implode('|',$items);
    }
}