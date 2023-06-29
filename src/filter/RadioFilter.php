<?php


namespace tpScriptVueCurd\filter;


use tpScriptVueCurd\ModelFilter;
use tpScriptVueCurd\traits\field\CheckField;
use think\db\Query;

/**
 * Class RadioFilter
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\filter
 */
class RadioFilter extends ModelFilter
{
    //是否FindInSet查询
    private bool $isFindInSet=false;

    //是否可多选
    private bool $isMore=false;
    //多选时筛选类型 OR 或者 AND
    private string $moreWhereType='OR';

    protected array $items=[];

    /**
     * @throws \think\Exception
     */
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
        if($value||$value===0||$value==='0'||($value===''&&in_array('',array_column($this->field->items(),'value'),true))){
            if($this->isFindInSet()){
                is_array($value)||$value=[$value];
                if(count($value)>1){
                    $whereArr=[];
                    foreach ($value as $v){
                        $whereArr[]='FIND_IN_SET("'.addslashes($v).'",`'.$this->field->name().'`)';
                    }
                    $query->whereRaw(implode(' '.$this->moreWhereType().' ',$whereArr));
                }else{
                    $query->whereFindInSet($this->field->name(),current($value));
                }
            }else{
                if(is_array($value)){
                    if($this->moreWhereType()==='OR'){
                        $query->whereIn($this->field->name(),$value);
                    }else{
                        throw new \think\Exception('查询的参数错误或应设置moreWhereType为OR');
                    }
                }else{
                    $query->where($this->field->name(),$value);
                }
            }



        }
    }


    /**
     * 查询方式
     * @param bool|null $isFindInSet
     * @return $this|bool
     */
    public function isFindInSet(bool $isFindInSet=null){
        if(is_null($isFindInSet)){
            return $this->isFindInSet;
        }
        $this->isFindInSet=$isFindInSet;
        return $this;
    }

    /**
     * 是否可多选
     * @param bool|null $isMore
     * @return $this|bool
     */
    public function isMore(bool $isMore=null){
        if(is_null($isMore)){
            return $this->isMore;
        }
        $this->isMore=$isMore;
        return $this;
    }


    /**
     * 是否可多选
     * @param string|null $moreWhereType
     * @return $this|string
     */
    public function moreWhereType(string $moreWhereType=null){
        if(is_null($moreWhereType)){
            return $this->moreWhereType;
        }
        $moreWhereType=strtoupper(trim($moreWhereType));
        if($moreWhereType!=='OR'&&$moreWhereType!=='AND'){
            throw new \think\Exception('传入参数错误');
        }
        $this->moreWhereType=$moreWhereType;
        return $this;
    }

    /**
     * 获取筛选配置
     * @return array
     */
    public function getConfig():array{
        $config=parent::getConfig();
        $config['isMore']=$this->isMore();
        return $config;
    }

    public static function componentUrl():string{
        return '/tpscriptvuecurd/filter/radio.js';
    }


}