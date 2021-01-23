<?php


namespace tpScriptVueCurd\traits\field;


use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\filter\RadioFilter;
use tpScriptVueCurd\ModelFilter;


/**
 * Trait CheckField
 * @package tpScriptVueCurd\traits\field
 * @author tj 1079798840@qq.com
 */
trait CheckField
{

    protected array $items=[];//选项集合
    private string $itemsSerialize='';


    /**
     * 选项集合(获取与设置)
     * @param array|null $items
     * @return $this|array
     */
    public function items(array $items=null){
        if(is_null($items)){
            return $this->items;
        }
        $this->items=$this->getItemsByItems($items);
        $this->itemsSerialize=serialize($this->items);
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
            isset($v['hideFields'])||$v['hideFields']=FieldCollection::make();//选中后隐藏的字段
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
            return array_values($items);
        }
        $arr=[];
        if(array_values($items)===$items){
            /** [ '张三','李四' ] **/
            foreach ($items as $v){
                $arr[]=['value'=>$v,'text'=>$v];
            }
        }else{
            /** [ 1=>'张三',2=>'李四','王五'=>'王五' ] **/
            foreach ($items as $k=>$v){
                $arr[]=['value'=>$k,'text'=>$v];
            }
        }
        return $arr;
    }


    final protected function getItemsTextValues():array{
        static $items=[];
        if(empty($items[$this->itemsSerialize]))$items[$this->itemsSerialize]=array_column($this->items,'value','text');
        return $items[$this->itemsSerialize];
    }

    final protected function getItemsValueTexts():array{
        static $items=[];
        if(empty($items[$this->itemsSerialize]))$items[$this->itemsSerialize]=array_column($this->items,'text','value');
        return $items[$this->itemsSerialize];
    }

    final protected function getItemsValue():array{
        static $items=[];
        if(empty($items[$this->itemsSerialize]))$items[$this->itemsSerialize]=array_column($this->items,'value');
        return $items[$this->itemsSerialize];
    }


    final protected function getShowText(string $val,bool $isMultiple){
        if($val===''){
            return '';
        }
        $textArr=[];
        if($isMultiple){
            foreach (explode(',',$isMultiple) as $v){
                $textArr[]=$this->getShowText($v,false);
            }
        }else{
            $textArr[]=$this->getItemsValueTexts()[$val];
        }
        return implode(',',$textArr);
    }

}