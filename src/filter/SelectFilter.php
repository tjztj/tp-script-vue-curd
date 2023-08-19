<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\field\CheckboxField;
use tpScriptVueCurd\FieldCollection;
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
    protected string $url='';//远程地址
    protected string $lableField='';
    protected string $valueField='';


    public function config():array{
        if(!$this->url&&method_exists($this->field,'url')&&!method_exists($this->field,'items')){
            $this->url=$this->field->url();
            if(method_exists($this->field,'fields')){
                $fields=$this->field->fields();
                if($this->valueField===''){
                    $this->valueField='id';
                }
                if($this->lableField===''){
                    if($fields instanceof FieldCollection){
                        $f=$fields[0];
                        $this->lableField=$f->name();
                    }else{
                        $this->lableField=key($fields);
                    }
                }
            }
        }
        if($this->url){
            return [
                'items'=>[],
                'url'=>$this->url,
                'multiple'=>$this->multiple(),
                'lableField'=>$this->lableField,
                'valueField'=>$this->valueField,
            ];
        }

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
            'url'=>'',
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


    public function setUrl(string $url,string $lableField,string $valueField): SelectFilter
    {
        $this->url=$url;
        $this->lableField=$lableField;
        $this->valueField=$valueField;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLableField(): string
    {
        return $this->lableField;
    }

    public function getValueField(): string
    {
        return $this->valueField;
    }

    public function setLableField(string $lableField): string
    {
        return $this->lableField=$lableField;
    }

    public function setValueField(string $valueField): string
    {
        return $this->valueField=$valueField;
    }


    public function generateWhere(Query $query,$value):void{
        if($value||$value===0||$value==='0'||($value===''&&  method_exists($this->field,'items')&&in_array('',array_column($this->field->items(),'value'),true))){
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
        return '/tpscriptvuecurd/filter/select.js';
    }

}