<?php


namespace tpScriptVueCurd\traits\field;


use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;


/**
 * Trait CheckField
 * @package tpScriptVueCurd\traits\field
 * @author tj 1079798840@qq.com
 */
trait CheckField
{

    use HideFields;

    protected array $items=[];//选项集合
    private string $itemsKey='';


    /**
     * 选项集合(获取与设置)
     * @param array|null $items
     * @return $this|array
     */
    public function items(array $items=null){
        if(is_null($items)){
            return $this->items;
        }
        //hideFields,选中此项时隐藏相关字段
        //hideItemBy,使用 FieldWhere。满足条件时显示此选项
        //[ 'value'=>'','text|title'=>'','hide'=>false,'hideFields'=>[field1,field2],'showItemBy'=>FieldWhere]
        $this->items=$this->getItemsByItems($items);
        $this->itemsKey=create_guid();
        return $this;
    }


    /**
     * 恩据传入的参数生成 items，并判断格式 是否正确
     * @param array $items
     * @return array
     * @throws \think\Exception
     */
    private function getItemsByItems(array $items):array{
        if(empty($items)){
            return [];
        }
        $items=$this->compileItems($items);
        foreach ($items as $k=>&$v){
            if(!isset($v['text'])&&isset($v['title'])){
                $v['text']=$v['title'];
            }
            if(isset($v['text'])&&!isset($v['title'])){
                $v['title']=$v['text'];
            }
            if($v['title']!==$v['text']){
                throw new \think\Exception('不可又设置text又设置title，只能设置一个或者他们相同');
            }
            //选中后隐藏的字段
            if(isset($v['hideFields']) && !$v['hideFields'] instanceof FieldCollection) {
                if($v['hideFields'] instanceof ModelField){
                    $v['hideFields']=FieldCollection::make([$v['hideFields']]);
                }else if(is_array($v['hideFields'])){
                    $v['hideFields']=FieldCollection::make($v['hideFields']);
                }
            }
            if(empty($v['hideFields'])||!$v['hideFields'] instanceof FieldCollection){
                $v['hideFields']=FieldCollection::make();
            }
        }
        return $items;
    }


    /**
     * items格式转换
     * @param array $items
     * @return array
     */
    private function compileItems(array $items):array{
        if(is_array(current($items))){
            //全局搜索 hideFields 查看例子
            /** [ ['value'=>1,'text'=>'张三','hideFields'=>[字段1,字段2]], ['value'=>2,'text'=>'李四'] ] **/
            $vals=[];
            foreach ($items as $v){
                $v['value']=(string)$v['value'];
                $vals[]=$v;
            }
            return $vals;
        }
        $arr=[];
        if(array_values($items)===$items){
            /** [ '张三','李四' ] **/
            foreach ($items as $v){
                $arr[]=['value'=>(string)$v,'text'=>(string)$v];
            }
        }else{
            /** [ 1=>'张三',2=>'李四','王五'=>'王五' ] **/
            foreach ($items as $k=>$v){
                $arr[]=['value'=>(string)$k,'text'=>(string)$v];
            }
        }
        return $arr;
    }


    /**
     * 获取选项 [val=>title,val=>title] 形式
     * @return array
     */
    final protected function getItemsTextValues():array{
        static $items=[];
        if(empty($items[$this->itemsKey]))$items[$this->itemsKey]=array_column($this->items,'value','text');
        return $items[$this->itemsKey];
    }

    /**
     * 获取所有选项的标题
     * @return array
     */
    final protected function getItemsValueTexts():array{
        static $items=[];
        if(empty($items[$this->itemsKey]))$items[$this->itemsKey]=array_column($this->items,'text','value');
        return $items[$this->itemsKey];
    }

    /**
     * 获取所有选项的值的数组
     * @return array
     */
    final protected function getItemsValue():array{
        static $items=[];
        if(empty($items[$this->itemsKey]))$items[$this->itemsKey]=array_column($this->items,'value');
        return $items[$this->itemsKey];
    }


    /**
     * 根据值获取显示的内容
     * @param string $val
     * @param bool $isMultiple
     * @return string
     */
    final protected function getShowText(string $val,bool $isMultiple): string
    {
        if($val===''){
            return $this->getItemsValueTexts()[$val]??'';
        }
        $textArr=[];
        if($isMultiple){
            foreach (explode(',',$val) as $v){
                $textArr[]=$this->getShowText($v,false);
            }
        }else{
            $textArr[]=$this->getItemsValueTexts()[$val]??$val;
        }
        return implode(',',$textArr);
    }


    /**
     * 选项中是否有 value=0 的选项
     * @return bool
     */
    final protected function haveZeroValue(): bool
    {
        foreach ($this->items as $v){
            if($v['value']==='0'||$v['value']===0){
                return true;
            }
        }
        return false;
    }

    public function toArray():array{
        $data=parent::toArray();
        foreach ($this->items as $k=>$v){
            if(isset($v['showItemBy'])&&is_object($v['showItemBy'])){
                if($this->objWellToArr){
                    $data['items'][$k]['showItemBy']=$v['showItemBy']->toArray();
                }else{
                    unset($data['items'][$k]['showItemBy']);
                }
            }
        }
        return $data;
    }

}