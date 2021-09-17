<?php

namespace tpScriptVueCurd\option\generate_table;

use think\Collection;
use think\facade\Db;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\traits\TableGetterSetter;

class GenerateTableOption extends Collection
{
    use TableGetterSetter;


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


    /**
     * 是否可修改列
     * @var bool
     */
    protected bool $modifyColumn=false;

    protected VueCurlModel $model;

    public function __construct(VueCurlModel $model)
    {
        $this->model=clone $model;
        parent::__construct($this->generateItems());
    }

    public static function make($items = [])
    {
        throw new \think\Exception('不能使用此方法');
    }


    public function generateItems():array{
        $arr=[];
        $this->model->fields()->each(function (ModelField $v)use(&$arr){
            if(!$v->generateSql()){
                return;
            }
            try{
                $field=new GenerateColumnOption($v->name());
            }catch (\Exception $e){
                throw new \think\Exception('字段'.$v->name().$e->getMessage().';如果不想生成此字段的Sql,请对字段->generateSql(false)');
            }

            $v->getColumnGenerateSqlConfig($field);
            if(empty($field->getComment())){
                $field->setComment(($v->group()?$v->group().'|':'').$v->title());
            }
            $arr[]=$field;
        });
        return $arr;
    }


    /**
     * 生成数据库操作语句
     *
     * @return string
     * @throws \think\Exception
     */
    public function getSql():string{
        $model=$this->model;

        if($this->isEmpty()){
            throw new \think\Exception(get_class($model) .'未在模型中定义字段');
        }
       if($this->engine!=='InnoDB'&&$this->engine!=='MyISAM'){
           throw new \think\Exception('engine不能设置为'.$this->engine.'，只能是InnoDB与MyISAM');
       }
       //重新设置字段
        $this->items = $this->convertToArray($this->generateItems());

        $controll=$model::getControllerClass();

        $tableName=$model->getTable();
        $tableOld=Db::table('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_SCHEMA',$model->getConfig('database'))
            ->where('TABLE_NAME',$tableName)->find();


        $comment=$this->comment;
        if(empty($comment)){
            $comment=$controll::getTitle();
        }


        if($tableOld){
            $before='';
            $hasFields=$model->getFields();


            if($controll::type()==='child'){
                $pf=new GenerateColumnOption($model::parentField());
                $pf->setTypeInt();
                $pf->setComment('父表ID');
                $this->push($pf);
            }

            $cols=[];
            $this->each(function (GenerateColumnOption $v)use(&$cols,&$before,$hasFields){
                if(isset($hasFields[$v->getName()])){
                    if(!$this->modifyColumn||!$v->checkIsChange($hasFields[$v->getName()])){
                        $before=$v->getName();
                        return;
                    }
                    $cols[]=$v->getSql('MODIFY',$before?:'id');
                }else{
                    $cols[]=$v->getSql('ADD',$before?:'id');
                }
                $before=$v->getName();
            });

            $beforeSql="ALTER TABLE `$tableName` \n ".implode(" ,\n ",$cols);

            $sql='';
            $engineChange=false;
            if(strtolower($this->engine)!==strtolower($tableOld['ENGINE'])){
                $sql.=" ,\n ENGINE=$this->engine";
                $engineChange=true;
            }
            if($comment!==$tableOld['TABLE_COMMENT']){
                if(!$engineChange){
                    $sql.=" ,\n";
                }else{
                    $sql.=" ,";
                }
                $sql.=" COMMENT = '".addslashes($comment)."'";
            }
            if($sql){
                $sql=$beforeSql.$sql.';';
            }else{
                $sql='';
            }
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