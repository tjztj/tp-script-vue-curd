<?php

namespace tpScriptVueCurd\traits\controller;

use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldStep;

/**
 * 流程图相关
 */
trait Bpmn
{

    /**
     * 显示流程图
     * @return mixed
     * @throws \think\Exception
     */
    public function bpmn(){
        $steps=[];
        $allSteps=[];
        $stepAlias=[];
        $this->fields->each(function (ModelField $field)use(&$steps,&$allSteps,&$stepAlias){
            foreach ($field->steps() as $v){
                /**
                 * @var FieldStep $v
                 */
                if(isset($allSteps[$v->getStep()])||!isset($v->getBeforeChecks)){
                    continue;
                }
                $allSteps[$v->getStep()]=$v;

                $stepName=$v->getStep();
                $befores=[];
                $stepHave=null;
                foreach (($v->getBeforeChecks)() as $key=>$val){
                    $beforeName=$key?$key::name():'';
                    $beforeInfo=[
                        'remark'=>$val->remark,
                        'name'=>$beforeName,
                    ];

                    if($val->title){
                        $stepHave===null&&$stepHave=false;
                        $sn=$stepName.'-'.$val->title;
                        if(!isset($steps[$sn])){
                            $steps[$sn]=[
                                'title'=>$val->title,
                                'name'=>$sn,
                                'source_name'=>$stepName,
                                'befores'=>[],
                            ];
                            isset($stepAlias[$stepName])||$stepAlias[$stepName]=[];
                            $stepAlias[$stepName][]=$sn;
                        }
                        $steps[$sn]['befores'][$beforeName]=$beforeInfo;
                    }else{
                        $stepHave=true;
                        $befores[$beforeName]=$beforeInfo;
                    }
                }
                if($stepHave||$stepHave===null){
                    $steps[$stepName]=[
                        'title'=>$v->getTitle(),
                        'name'=>$stepName,
                        'befores'=>$befores,
                    ];
                }
            }
        });

        $data=[
            // 点集
            'nodes'=>[],
            // 边集
            'edges'=>[],
        ];


        $nextSteps=[];
        foreach ($steps as $k=>$v){
            foreach ($v['befores'] as $key=>$val){
                isset($nextSteps[$key])||$nextSteps[$key]=[];
                $nextSteps[$key][]=$k;
                if(!empty($stepAlias[$key])){
                    foreach ($stepAlias[$key] as $vv){
                        $nextSteps[$vv][]=$k;
                    }
                }
            }
        }

        foreach ($steps as $k=>$v){
            $isFirst=false;
            foreach ($v['befores'] as $key=>$val){
                if($key===''){
                    $isFirst=true;
                    continue;
                }

                if($key!==$k){
                    $data['edges'][]=['source'=>$key,'target'=>$k,'label'=>$val['remark']];
                    if(!empty($stepAlias[$key])){
                        foreach ($stepAlias[$key] as $vv){
                            $data['edges'][]=['source'=>$vv,'target'=>$k,'label'=>$val['remark']];
                        }
                    }
                }
            }
            $node=['id'=>$k,'label'=>$v['title'],'type'=>$isFirst||empty($nextSteps[$k])?'rect':'ellipse'];
            if($node['type']==='rect'){
                $node['style']['radius']=2;
            }
            $data['nodes'][]=$node;
        }

        return $this->fetch(getVCurdDir().'tpl/step/bpmn.vue',[
            'data'=>$data,
            'jsPath'=>'/tpscriptvuecurd/actions/step.js',
        ]);
    }
}