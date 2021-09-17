<?php

namespace tpScriptVueCurd\option\generate_table;

use think\Collection;
use think\facade\Db;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\option\generate_table\traits\TableGetterSetter;

class GenerateTableOption extends Collection
{
    use TableGetterSetter;

    /**
     * @var string 表名（不包含前缀）
     */
    protected string $name;


    /**
     * @var string 数据库引擎
     */
    protected string $engine='InnoDB';

    /**
     * @var string 表备注
     */
    protected string $comment='';

    /**
     * 字段集合
     * @var GenerateColumnOption[]
     */
    protected $items = [];


    public function getSql(VueCurlModel $model,bool $canEditColumn=false):string{
        if($this->isEmpty()){
            throw new \think\Exception(get_class($model) .'未在模型中定义字段');
        }
       if($this->engine!=='InnoDB'&&$this->engine!=='MyISAM'){
           throw new \think\Exception('engine不能设置为'.$this->engine.'，只能是InnoDB与MyISAM');
       }

        $controll=$model::getControllerClass();

        $tableName=$model->getTable();
        $hasTable=!empty(Db::table('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_SCHEMA',$model->getConfig('database'))
            ->where('TABLE_NAME',$tableName)->value('TABLE_NAME'));


        $comment=$this->comment;
        if(empty($comment)){
            $comment=$controll::getTitle();
        }


        if($hasTable){
            $before='';
            $hasFieldsKey=array_flip($model->getTableFields());



            if($controll::type()==='child'){
                $pf=new GenerateColumnOption($model::parentField());
                $pf->setTypeInt();
                $pf->setComment('父表ID');
                $this->push($pf);
            }

            $cols=[];
            $this->each(function (GenerateColumnOption $v)use(&$cols,&$before,$hasFieldsKey,$canEditColumn){
                $cols[]=$v->getSql(isset($hasFieldsKey[$v->getName()])&&$canEditColumn?'MODIFY':'ADD',$before?:'id');
                $before=$v->getName();
            });
            $sql="ALTER TABLE `$tableName` \n ".implode(" ,\n ",$cols)." ,\n ENGINE=$this->engine , COMMENT = '".addslashes($comment)."';";
        }else{
            $cols=[];
            $this->each(function (GenerateColumnOption $v)use(&$cols){
                $cols[]=$v->getSql('ADD');
            });


            $patentField='';
            if($controll::type()==='child'){
                $patentField="`".$model::parentField()."` int(11) NOT NULL DEFAULT 0 COMMENT '项目ID',";
            }

            $stepFields='';
            if($model->fields()->stepIsEnable()){
                $stepFields="  `".$model::getStepField()."` json NOT NULL COMMENT '步骤',
  `".$model::getNextStepField()."` varchar(30) NOT NULL DEFAULT '' COMMENT '下一步',
  `".$model::getCurrentStepField()."` varchar(30) NOT NULL DEFAULT '' COMMENT '当前步骤',
  `".$model::getStepPastsField()."` varchar(255) NOT NULL DEFAULT '' COMMENT '已走步骤',";

            }



            $sql="CREATE TABLE `$tableName`  ( `id` int NOT NULL AUTO_INCREMENT, \n ".implode(" ,\n ",$cols).", \n 
            $patentField
$stepFields
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `create_system_admin_id` int(11) NOT NULL DEFAULT 0 COMMENT '创建人',
  `update_time` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` int(10) NOT NULL DEFAULT 0 COMMENT '删除时间',
  `delete_system_admin_id` int(11) NOT NULL DEFAULT 0 COMMENT '删除人',
  `update_system_admin_id` int(11) NOT NULL DEFAULT 0 COMMENT '修改人',
  PRIMARY KEY (`id`)
); ENGINE=$this->engine , COMMENT = '".addslashes($this->comment)."';";

        }
        return $sql;
    }

}