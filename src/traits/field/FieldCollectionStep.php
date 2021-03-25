<?php


namespace tpScriptVueCurd\traits\field;


use tpScriptVueCurd\ModelField;

trait FieldCollectionStep
{

    private array $stepConfig=[
        'enable'=>false,
    ];
    public function step($stepConfig):self{
        if($stepConfig===false){
            $this->stepConfig['enable']=false;
            return $this;
        }
        $this->each(function(ModelField $v){
            if(!$v->steps()){
                throw new \think\Exception('字段'.$v->name().'未配置steps');
            }
        });
        if($stepConfig===true){
            $this->stepConfig['enable']=true;
            return $this;
        }
        if(!isset($stepConfig['enable'])){
            $stepConfig['enable']=true;
        }
        $this->stepConfig=$this->mergeArrays($this->stepConfig,$stepConfig);
        return $this;
    }


    public function stepIsEnable():bool{
        return $this->stepConfig['enable'];
    }

    public function getStepConfig():array{
        return $this->stepConfig;
    }


    /**
     * 递归合并两个数组
     * @param array $old
     * @param array $new
     * @return array
     */
    private function mergeArrays(array $old,array $new):array{
        foreach ($old as $k=>$v){
            if(isset($new[$k])){
                if(is_array($v)&&is_array($new[$k])){
                    $v=$this->mergeArrays($v,$new[$k]);
                }else{
                    $v=$new[$k];
                }
                $old[$k]=$v;
            }
        }
        foreach ($new as $k=>$v){
            if(!isset($old[$k])){
                $old[$k]=$v;
            }
        }
        return $old;
    }
}