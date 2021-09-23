<?php

namespace tpScriptVueCurd\filter;

use think\db\Query;
use tpScriptVueCurd\field\TreeSelect;
use tpScriptVueCurd\ModelFilter;

class CascaderFilter extends ModelFilter
{
    protected array $items=[];

    protected string $valueName='value';
    protected string $pvalueName='pvalue';
    protected string $childrenName='children';

    /**
     * @param string $valueName
     * @return $this
     */
    public function setValueName(string $valueName): self
    {
        $this->valueName = $valueName;
    }
    /**
     * @param string $pvalueName
     * @return $this
     */
    public function setPvalueName(string $pvalueName): self
    {
        $this->pvalueName = $pvalueName;
    }
    /**
     * @param string $childrenName
     * @return $this
     */
    public function setChildrenName(string $childrenName): self
    {
        $this->childrenName = $childrenName;
    }




    public function setItems(array $items):self{
        if(empty($items)){
            $this->items=[];
            return $this;
        }
        $this->items=TreeSelect::listToTree(TreeSelect::treeToList($items,$this->valueName,$this->childrenName),$this->valueName,$this->pvalueName,$this->childrenName);
        return $this;
    }

    public function getItems(){
        if(empty($this->items)){
            if(method_exists($this->field,'items')){
                $this->setItems($this->field->items());
            }
        }
        return $this->items;
    }

    protected function config():array{
        return [
            'items'=>$this->getItems(),
        ];
    }

    public function generateWhere(Query $query,$value):void{
        if(!isset($this->lists)){
            $this->lists=TreeSelect::treeToList($this->getItems(),$this->valueName,$this->childrenName);
        }
        
        if($value||$value===0||$value==='0'){
            $canCheckParent=method_exists($this->field,'canCheckParent')&&$this->field->canCheckParent();
            if(method_exists($this->field,'multiple')&&$this->field->multiple()){
                if($canCheckParent){
                    $query->whereFindInSet($this->field->name(),$value);
                }else{
                    $query->where(function (Query $q)use($value){
                        foreach ($this->lists[$value]['childLastVals']?:[$value] as $v){
                            $q->whereFindInSet($this->field->name(),$v,'OR');
                        }
                    });
                }
            }else{
                if($canCheckParent){
                    $query->where($this->field->name(),$value);
                }else{
                    $query->whereIn($this->field->name(),$this->lists[$value]['childLastVals']?:[$value]);
                }
            }
        }
    }

    static public function componentUrl():string{
        return '/tp-script-vue-curd-static.php?filter/cascader.js';
    }
}