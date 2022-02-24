<?php


namespace tpScriptVueCurd;


use tpScriptVueCurd\filter\EmptyFilter;
use think\db\Query;
use tpScriptVueCurd\traits\Func;

/**
 * Class ModelFilter
 * @package tpScriptVueCurd
 * @author tj 1079798840@qq.com
 */
abstract class ModelFilter
{
    use Func;

    protected ModelField $field;
    protected string $type;
    protected bool $show=true;//默认显示

    public function __construct(ModelField $field=null){
        if(static::class===EmptyFilter::class){
            return;
        }
        if(is_null($field)){
            throw new \think\Exception('请传入字段对象');
        }
        $this->field=&$field;
        $this->type=class_basename(static::class);
    }


    /**
     * 获取子类配置信息
     * @return array
     */
    abstract protected function config():array;
    /**
     * 获取筛选配置
     * @return array
     */
    public function getConfig():array{
        $config=$this->config();
        $config['name']=$this->field->name();
        $config['title']=$this->field->title();
        $config['fieldType']=$this->field->getType();
        $config['group']=$this->field->group();
        $config['type']=$this->getType();
        $config['show']=$this->getShow();
        return $config;
    }

    public function getType():string{
        return $this->type;
    }

    /**
     * 默认显示
     * @return string
     */
    public function getShow():string{
        return $this->show;
    }


    /**
     * 是否在列表默认显示
     * @param bool $show
     * @return $this
     */
    public function setShow(bool $show):self{
        $this->show=$show;
        return $this;
    }







    /**
     * 生成where条件
     * @param Query $query
     * @param $value
     */
    abstract public function generateWhere(Query $query,$value):void;

    /**
     * 组件地址
     * @return string
     */
    abstract public static function componentUrl():string;
}