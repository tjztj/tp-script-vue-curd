<?php


namespace tpScriptVueCurd;


use think\Collection;
use think\db\Query;
use tpScriptVueCurd\option\FieldNumHideField;
use tpScriptVueCurd\option\FieldNumHideFieldCollection;
use tpScriptVueCurd\traits\Func;

/**
 * 字段集合
 * Class FieldCollection
 * @package tpScriptVueCurd
 * @author tj 1079798840@qq.com
 */
class FieldCollection extends Collection
{
    use Func;
    public array $groupItems=[];//字段分组（如果少于2个组，将为空；字段不设置group，将赋值为 基本信息）
    public function __construct($items = [])
    {
        $groupItems=self::groupListByItems($items);
        if(count($groupItems)>1){
            $this->groupItems=$groupItems;
        }
        parent::__construct($items);
    }


    /**
     * 根据字段信息获取分组信息（写在这里主要是为了方便字段集合 toArray 后调用）
     * @param array $items
     * @return array
     */
    public static function groupListByItems(array $items):array{
        $groupItems=[];
        foreach ($items as $v){
            $group=is_array($v)?$v['group']:$v->group();
            $group||$group='基本信息';
            isset($groupItems[$group])||$groupItems[$group]=[];
            $groupItems[$group][]=$v;
        }
        return $groupItems;
    }


    /**
     * 获取所有列表中要显示出来的字段集合
     * @return $this
     */
    public function listShowItems(): self
    {
        return $this->filter(fn($v)=>$v->listShow());
    }



    public function toArray(): array{
        return array_map(static function ($value) {
            return $value instanceof ModelField ? $value->toArray() : $value;
        }, $this->items);
    }




    /**
     * 设置保存的值
     * @param array $data
     * @param bool $isExcelDo  是否EXCEL添加
     * @return $this
     * @throws \think\Exception
     */
    public function setSave(array $data,bool $isExcelDo=false): self
    {
        $this->filterHideFieldsByData($data)->each(function(ModelField $v)use($data,$isExcelDo){
            try{
                if($isExcelDo){
                    $v->excelSaveDoData($data);
                }
                $v->setSave($data);
            }catch (\Exception $e){
                throw new \think\Exception($v->title().'：'.$e->getMessage());
            }
        });
        return $this;
    }

    /**
     * 返回数据中指定的一列
     * @access public
     * @param string|null $columnKey 键名
     * @param string|null $indexKey  作为索引值的列
     * @return array
     */
    public function column(?string $columnKey, string $indexKey = null): array
    {
        return array_column($this->toArray(), $columnKey, $indexKey);
    }

    /**
     * 获取要保存的信息
     * @return array
     */
    public function getSave():array{

        return $this->filter(function(ModelField $v){
            //如果是null,代表没有赋值
            return !is_null($v->getSave())&&!$v->readOnly();
        })
            ->column('save','name');
    }


    /**
     * 根据字段name获取字段信息
     * @param string $name
     * @return ModelField
     */
    public function findByName(string $name):ModelField{
        foreach ($this->items as $item) {
            if($item->name()===$name){
                return $item;
            }
        }
        throw new \think\Exception('未找到字段:'.$name);
    }
    /**
     * 根据字段name获取字段信息
     * @param string $name
     * @return ModelField
     */
    public function getFieldByNmae(string $name):ModelField{
        return $this->findByName($name);
    }



    /**
     * 处理要显示的数据
     * @param array $data
     */
    public function doShowData(array &$data): void
    {
        $this->each(function(ModelField $v)use(&$data){
            $v->doShowData($data);
        });
    }


    /**
     * 获取字段筛选集合
     * @return array
     */
    public function getFilterItems(): array
    {
        $list=[];
        $this->each(function(ModelField $v)use(&$list){
            $filter=$v->filter();
            is_null($filter)||$list[]=$v->filter();
        });

        return $list;
    }


    /**
     * 获取筛选配置
     * @return array
     */
    public function getFilterShowData():array{
        $configs=[];
        foreach ($this->getFilterItems() as $v){
            $configs[]=$v->getConfig();
        }
        return $configs;
    }



    /**
     * 生成查询条件
     * @param null|string|array $myFilter 传入参数和自己获取两种方法(json或数组或空)
     * @return array|\Closure
     */
    public function getFilterWhere(array $myFilter=null){
        if(is_null($myFilter)){
            $myFilter=input('filterData',null,null);
            if(empty($myFilter)){
                //支持下划线写法
                $myFilter=input('filter_data',null,null);
                if(empty($myFilter)){
                    return [];
                }
            }
        }
        if(is_string($myFilter)){
            $myFilter=json_decode($myFilter,true);
        }
        if(empty($myFilter)){
            return [];
        }


        return function(Query $query)use($myFilter){
            if(isset($myFilter['id'])){
                //可以使用ID查找
                $query->where('id','in',is_array($myFilter['id'])?$myFilter['id']:explode(',',$myFilter['id']));
            }
            $this->each(function(ModelField $v)use($query,$myFilter){
                $filter=$v->filter();
                if(is_null($filter)){
                    return;
                }
                $name=$v->name();
                if(isset($myFilter[$name])){
                    $filter->generateWhere($query,$myFilter[$name]);
                }
            });
        };
    }


    /**
     * 过滤掉【hideFields】不显示的字段
     * @param array $data  用户提交上来的数据
     * @return $this
     */
    private function filterHideFieldsByData(array $data,$isDataBaseInfo=true):self{
        $arrHave= static function($arr, $val){
            if(is_string($arr)){
                $arr=explode(',',$arr);
            }
            $arr=array_filter($arr);
            $arr=array_map(static fn($v)=>(string)$v,$arr);
            return in_array((string)$val,$arr,true);
        };


        $fieldHideList=[];//【hideFields】不显示的字段


        $changeFieldHideList=function($key,$fieldName,$hide)use(&$fieldHideList) {
            if ($hide) {
                isset($fieldHideList[$key]) || $fieldHideList[$key] = [];
                $fieldHideList[$key][] = $fieldName;
                return;
            }
            if (!isset($fieldHideList[$key])) {
                return;
            }
            if (count($fieldHideList[$key]) > 0) {
                $fieldHideList[$key] = array_filter($fieldHideList[$key], fn($v) => $v !== $fieldName);
            }
            if (count($fieldHideList[$key]) === 0) {
                unset($fieldHideList[$key]);
            }
        };
        return $this->each(function(ModelField $v)use($arrHave,$data,$changeFieldHideList,$isDataBaseInfo){
            $vName=$v->name();
            $vType=$v->getType();

            /*** 获取【hideFields】不显示的字段 ***/
            if(method_exists($v,'hideFields')){
                /**
                 * @var FieldNumHideFieldCollection $hideFields
                 */
                $hideFields=$v->hideFields();
                if(is_null($hideFields)||!isset($data[$vName])){
                    return;
                }
                if($isDataBaseInfo){
                    $vValue=$data[$vName];
                }else{
                    $fieldCopy=clone $v;
                    $vValue=$fieldCopy->setSave($data)->getSave();
                }
                $hideFields->getAccordWithFieds($vValue)->each(function(FieldNumHideField $v)use($changeFieldHideList,$vName){
                    $v->getFields()->each(function($f)use($changeFieldHideList,$vName){
                        $changeFieldHideList($f->name(),$vName,true);
                    });
                });
                $hideFields->getNotAccordWithFieds($vValue)->each(function(FieldNumHideField $v)use($changeFieldHideList,$vName){
                    $v->getFields()->each(function($f)use($changeFieldHideList,$vName){
                        $changeFieldHideList($f->name(),$vName,false);
                    });
                });
            }else if(method_exists($v,'items')){
                if(!isset($data[$vName])){
                    return;
                }
                $vValue=null;
                foreach ($v->items() as $item){
                    if(!isset($item['hideFields'])){
                        continue;
                    }
                    if(is_null($vValue)){
                        if($isDataBaseInfo){
                            $vValue=$data[$vName];
                        }else{
                            $fieldCopy=clone $v;
                            $vValue=$fieldCopy->setSave($data)->getSave();
                        }
                    }
                    $item['hideFields']->each(function(ModelField $hidelField)use($changeFieldHideList,$vName,$vType,$vValue,$arrHave,$item,$v){
                        //与JS中的一致
                        switch ($vType) {
                            case 'CheckboxField':
                                $hide=$arrHave($vValue, $item['value']);
                                break;
                            case 'SelectField':
                                if ($v->multiple()) {
                                    $hide=$arrHave($vValue, $item['value']);
                                } else {
                                    $hide=(string)$vValue === (string)$item['value'];
                                }
                                break;
                            default:
                                $hide= (string)$vValue === (string)$item['value'];
                        }
                        $changeFieldHideList($hidelField->name(),$vName,$hide);
                    });
                }
            }
        })->filter(function(ModelField $v)use($fieldHideList){
            /*** 过滤掉【hideFields】不显示的字段 ***/
            $name=$v->name();
            if(!isset($fieldHideList[$name])){
                //没有设置代表无限制
                return true;
            }
            return false;
        });
    }


    /**
     * 获取字段相关模板内容 url
     * @param string $type
     * @return array
     */
    public function getComponents(string $type):array{
        $return=[];
        $this->each(function(ModelField $field)use(&$return,$type){
            if(isset($return[$field->name()])){
                return;
            }
            if(!in_array($type,['index','show','edit'])){
                return;
            }
            $tpl=$field::componentUrl();
            isset($tpl->$type)&&$return[$field->name()]=$tpl->toArray($tpl->$type);
            if($field->getType()==='ListField'){
                foreach ($field->fields()->getComponents($type) as $k=>$v){
                    $return[$field->name().'['.$k.']']=$v;
                }
                $return=array_merge($return,);
            }
        });
        return $return;
    }


    /**
     * 获取字段筛选组件 url
     * @return array
     */
    public function getFilterComponents():array{
        $return=[];
        $this->each(function(ModelField $field)use(&$return){
            is_null($field->filter())||$return[$field->filter()->getType()]=$field->filter()::componentUrl();
        });
        return array_filter($return);
    }
}