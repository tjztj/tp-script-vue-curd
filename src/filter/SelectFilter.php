<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\field\CheckboxField;
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
    protected bool $multiple=false;

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
                throw new \think\Exception('字段'.$this->field->name().' items 不可未空');
            }
        }
        return [
            'items'=>$this->items,
            'multiple'=>$this->multiple(),
        ];
    }


    public function multiple(bool $multiple=null){
        if($multiple===null){
            return $this->multiple;
        }
        $this->multiple=$multiple;
        return $this;
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
        if($value||$value===0||$value==='0'||($value===''&&in_array('',array_column($this->field->items(),'value'),true))){
            if($this->field instanceof CheckboxField||is_subclass_of($this->field,CheckboxField::class)||(method_exists($this->field,'multiple')&&$this->field->multiple())){
                if(is_array($value)){
                    if($this->multiple()){
                        $query->where(function(Query $q)use($value){
                            foreach ($value as $v){
                                $q->whereFindInSet($this->field->name(),$v);
                            }
                        });
                    }else{
                        throw new \think\Exception('筛选的值错误');
                    }
                }else{
                    $query->whereFindInSet($this->field->name(),$value);
                }
            }else{
                if(is_array($value)){
                    if($this->multiple()){
                        $query->whereIn($this->field->name(),$value);
                    }else{
                        throw new \think\Exception('筛选的值错误');
                    }
                }else{
                    $query->where($this->field->name(),$value);
                }
            }


        }
    }

    public static function componentUrl():string{
        return '/tp-script-vue-curd-static.php?filter/select.js';
    }

}