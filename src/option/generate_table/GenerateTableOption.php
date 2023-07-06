<?php

namespace tpScriptVueCurd\option\generate_table;

use think\Collection;
use think\facade\Db;
use tpScriptVueCurd\base\model\BaseModel;
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


    /**
     * @return string
     */
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * @param string $engine
     */
    public function setEngine(string $engine): void
    {
        $this->engine = $engine;
    }

    protected BaseModel $model;

    public function __construct(BaseModel $model)
    {
        $this->model=clone $model;
        parent::__construct($this->generateItems());
    }

    public static function make($items = [])
    {
        throw new \think\Exception('不能使用此方法');
    }


    /**
     * 生成字段列表
     * @return array
     * @throws \think\Exception
     */
    public function generateItems():array{
        $arr=[];
        $this->model->fields()->each(function (ModelField $v)use(&$arr){
            $generateColumn=$v->generateColumn();
            if(!$generateColumn){
                return;
            }
            try{
                $field=new GenerateColumnOption($v->name());
            }catch (\Exception $e){
                throw new \think\Exception('字段'.$v->name().$e->getMessage().';如果不想生成此字段的Sql,请对字段->generateColumn(false)');
            }

            $v->getGenerateColumnConfig($field);
            if(is_callable($generateColumn)&&$generateColumn($field)===false){
                return;
            }
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

        if(empty($model->controller)){
            return '';
        }

        $controll=$model->controller;
        if($controll->getParentController()){
            $pf=new GenerateColumnOption($model::parentField());
            $pf->setTypeInt();
            $pf->setComment('父表ID');
            $this->push($pf);
        }

        if($model->fields()->stepIsEnable()){
            $pf=new GenerateColumnOption($model::getStepField());
            $pf->setTypeJson();
            $pf->setComment('步骤');
            $this->push($pf);
            $pf=new GenerateColumnOption($model::getNextStepField());
            $pf->setTypeVarchar(30);
            $pf->setComment('下一步');
            $this->push($pf);
            $pf=new GenerateColumnOption($model::getCurrentStepField());
            $pf->setTypeVarchar(30);
            $pf->setComment('当前步骤');
            $this->push($pf);
            $pf=new GenerateColumnOption($model::getStepPastsField());
            $pf->setTypeVarchar(600);
            $pf->setComment('已走步骤');
            $this->push($pf);
        }


        $tableName=$model->getTable();
        $tableOld=Db::table('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_SCHEMA',$model->getConfig('database'))
            ->where('TABLE_NAME',$tableName)->find();


        $comment=$this->comment;
        if(empty($comment)){
            $comment=$controll->title;
        }


        //字段重名过滤
        $colByNameArr=[];
        foreach ($this->items as $k=>$v){
            $sql=$v->getSql('');
            if(isset($colByNameArr[$v->getName()])){
                if($colByNameArr[$v->getName()]!==$sql){
                    throw new \think\Exception('数据库'.$tableName.'表自动生成字段失败：'.$v->getName().'字段配置存在多个且字段的类型或注释不一样');
                }
                unset($this->items[$k]);
            }else{
                $colByNameArr[$v->getName()]=$sql;
            }
        }


        if($tableOld){
            $before='';
            $hasFields=$model->getFields();



            $cols=[];
            $this->each(function (GenerateColumnOption $v)use(&$cols,&$before,$hasFields){
                if(isset($hasFields[$v->getName()])){
                    $v->changeFieldTypeByOldField($hasFields[$v->getName()]);

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

            $beforeSql="ALTER TABLE `$tableName` ";


            $colsSql=$cols?" \n ".implode(" ,\n ",$cols):'';


            $sql='';
            $engineChange=false;
            if(strtolower($this->engine)!==strtolower($tableOld['ENGINE'])){
                if($cols){
                    $sql.=" , \n";
                }
                $sql.=" ENGINE=$this->engine";
                $engineChange=true;
            }
            if($comment!==$tableOld['TABLE_COMMENT']){
                if(!$engineChange){
                    if($cols){
                        $sql.=" ,\n";
                    }else{
                        $sql= " \n";
                    }
                }else if($cols){
                    $sql.=" ,";
                }
                $sql.=" COMMENT = '".addslashes($comment)."'";
            }
            if($sql||$colsSql){
                $sql=$beforeSql.$colsSql.$sql.';';
            }else{
                $sql='';
            }
        }else{

            $sql="CREATE TABLE `$tableName`  ( `id` int NOT NULL AUTO_INCREMENT, \n ".implode(" ,\n ",$colByNameArr).", \n 
  `create_time` bigint(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `".$model::getCreateLoginUserField()."` int(11) NOT NULL DEFAULT 0 COMMENT '创建人',
  `update_time` bigint(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `delete_time` bigint(10) NOT NULL DEFAULT 0 COMMENT '删除时间',
  `".$model::getDeleteLoginUserField()."` int(11) NOT NULL DEFAULT 0 COMMENT '删除人',
  `".$model::getUpdateLoginUserField()."` int(11) NOT NULL DEFAULT 0 COMMENT '修改人',
  PRIMARY KEY (`id`)
) ENGINE=$this->engine , COMMENT = '".addslashes($comment)."';";

        }
        return $sql;
    }

}