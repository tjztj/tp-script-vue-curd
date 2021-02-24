<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use tpScriptVueCurd\traits\field\CheckField;
use think\db\Query;

/**
 * Class SelectFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class SelectFilter extends ModelFilter
{
    protected array $items=[];

    public function config():array{
        if(empty($this->items)){
            if(method_exists($this->field,'items')){
                foreach ($this->field->items() as $v){
                    $this->items[]=[
                        'value'=>$v['value'],
                        'title'=>$v['text'],
                        'text'=>$v['text'],
                    ];
                }
            }else{
                throw new \think\Exception('字段'.$this->field->name().'设置的filter错误，此filter需 字段 继承'.(CheckField::class));
            }
            if(empty($this->items)){
                throw new \think\Exception('字段'.$this->field->name().'items 不可未空');
            }
        }
        return [
            'items'=>$this->items,
        ];
    }


    /**
     * 设置筛选选项
     * @param array $items
     * @return $this
     */
    public function setItems(array $items): self
    {
        foreach ($items as $k=>$v){
            $items[$k]['text']=$v['title'];
        }

        $this->items=$items;
        return $this;
    }

    public function generateWhere(Query $query,$value):void{
        if($value||$value===0||$value==='0'){
            if(method_exists($this->field,'multiple')&&$this->field->multiple()){
                $query->whereFindInSet($this->field->name(),$value);
            }else if(is_array($value)){
                $query->whereIn($this->field->name(),$value);
            }else{
                $query->where($this->field->name(),$value);
            }
        }
    }

}