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
        $this->fields->each(function (ModelField $field)use(&$steps){
            foreach ($field->steps() as $v){
                /**
                 * @var FieldStep $v
                 */
                if(isset($steps[$v->getStep()])||!isset($v->getBeforeChecks)){
                    continue;
                }
                $befores=[];
                foreach (($v->getBeforeChecks)() as $key=>$val){
                    $befores[$key]=[
                        'remark'=>$val->remark
                    ];
                }
                $steps[$v->stepClass??$v->getStep()]=[
                    'title'=>$v->getTitle(),
                    'name'=>$v->getStep(),
                    'befores'=>$befores,
                ];
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
            }
        }

        foreach ($steps as $k=>$v){
            $isFirst=false;
            foreach ($v['befores'] as $key=>$val){
                if($key===''){
                    $isFirst=true;
                    continue;
                }
                $data['edges'][]=['source'=>$key,'target'=>$k,'label'=>$val['remark']];
            }
            $node=['id'=>$k,'label'=>$v['title'],'type'=>$isFirst||empty($nextSteps[$k])?'rect':'ellipse'];
            if($node['type']==='rect'){
                $node['style']['radius']=2;
            }
            $data['nodes'][]=$node;
        }

        return $this->fetch(getVCurdDir().'tpl/step/bpmn.vue',[
            'data'=>$data,
            'jsPath'=>'/tp-script-vue-curd-static.php?actions/step.js',
        ]);
    }
}