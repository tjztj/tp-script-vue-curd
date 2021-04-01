<?php


namespace tpScriptVueCurd;


use think\Collection;
use think\db\Query;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\field\PasswordField;
use tpScriptVueCurd\option\FieldNumHideField;
use tpScriptVueCurd\option\FieldNumHideFieldCollection;
use tpScriptVueCurd\traits\field\FieldCollectionStep;
use tpScriptVueCurd\traits\Func;

/**
 * 字段集合
 * Class FieldCollection
 * @package tpScriptVueCurd
 * @author tj 1079798840@qq.com
 */
class FieldCollection extends Collection
{
    use Func,FieldCollectionStep;
    private bool $saveHideFieldSetNull=true;//当字段未隐藏时，设置字段的值为空
    public array $groupItems=[];//字段分组（如果少于2个组，将为空；字段不设置group，将赋值为 基本信息）
    public function __construct($items = [])
    {
        $this->setGroupItems($items);
        parent::__construct($items);
    }

    private function setGroupItems(array $items){
        $groupItems=self::groupListByItems($items);
        if(count($groupItems)>1){
            $this->groupItems=$groupItems;
        }else{
            $this->groupItems=[];
        }
    }

    /**
     * 重新生成 分组
     * @return $this
     */
    public function rendGroup():self{
        $this->setGroupItems($this->items);
        return $this;
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


    public function setSaveHideFieldSetNull(bool $saveHideFieldSetNull):self{
        $this->saveHideFieldSetNull=$saveHideFieldSetNull;
        return $this;
    }

    public function getSaveHideFieldSetNull():bool{
        return $this->saveHideFieldSetNull;
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
        if($isExcelDo){
            //先处理一遍数据
            $this->each(function(ModelField $v)use(&$data){
                try{
                    $v->excelSaveDoData($data);
                }catch (\Exception $e){
                    throw new \think\Exception($v->title().'：'.$e->getMessage());
                }
            });
        }


        $nullNames=[];
        if($this->getSaveHideFieldSetNull()){
            //隐藏的字符串要设置为空
            $nullNames=$this->filterHideFieldsByData($data)->column('name');
            $fields=$this;
        }else{
            $fields=$this->filterHideFieldsByData($data);
        }
        $fields->each(function(ModelField $v)use($data,$nullNames){
            try{
                if(in_array($v->name(),$nullNames,true)){
                    $v->required(false);
                    $v->setSaveToNull();
                }else{
                    $v->setSave($data);
                }
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
            if($v instanceof PasswordField){
                return ;
            }
            $data['_Original_'.$v->name()]=$data[$v->name()];//原数据
        });
        $this->each(function(ModelField $v)use(&$data){
            $v->doShowData($data);
        });
    }


    /**
     * 查看页面过滤掉 隐藏的字段
     * @param VueCurlModel $sourceData
     * @return array
     */
    public function filterHideFieldsByShow(VueCurlModel $sourceData){
        return $this->filterHideFieldsByData($sourceData->toArray(),true);
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
    private function filterHideFieldsByData(array $data,$isSourceData=true):self{
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


        $checkHideField=function($field,$checkVal)use($arrHave,&$data,&$changeFieldHideList,$isSourceData,&$checkHideField,&$fieldHideList){
            $vName=$field->name();
            $vType=$field->getType();
            $reversalHideFields=method_exists($field,'reversalHideFields')&&$field->reversalHideFields()===true;
            $oldHideFields=array_keys($fieldHideList);

            /*** 获取【hideFields】不显示的字段 ***/
            if(method_exists($field,'hideFields')){
                /**
                 * @var FieldNumHideFieldCollection $hideFields
                 */
                $hideFields=$field->hideFields();
                if(is_null($hideFields)){
                    return;
                }
                $vValue=$checkVal;
                if(!$isSourceData&&!is_null($checkVal)){//因为我可能强制设了 $vValue 为null,不让它显示
                    $fieldCopy=clone $field;
                    $vValue=$fieldCopy->setSave($data)->getSave();
                }


                //有值才显示
                if($vValue){
                    //当是反转时，隐藏变为现实，显示变为隐藏，但不会对defHideAboutFields执行
                    $hideVal=!$reversalHideFields;

                    $hideFields->getAccordWithFieds($vValue)->each(function(FieldNumHideField $v)use($changeFieldHideList,$vName,$hideVal){
                        $v->getFields()->each(function($f)use($changeFieldHideList,$vName,$hideVal){
                            $changeFieldHideList($f->name(),$vName,$hideVal);
                        });
                    });
                    $hideFields->getNotAccordWithFieds($vValue)->each(function(FieldNumHideField $v)use($changeFieldHideList,$vName,$hideVal){
                        $v->getFields()->each(function($f)use($changeFieldHideList,$vName,$hideVal){
                            $changeFieldHideList($f->name(),$vName,$hideVal);
                        });
                    });
                }else if(method_exists($field,'defHideAboutFields')&&$field->defHideAboutFields()){ //默认隐藏所有
                    $hideFields->each(function(FieldNumHideField $v)use($changeFieldHideList,$vName){
                        $v->getFields()->each(function($f)use($changeFieldHideList,$vName){
                            $changeFieldHideList($f->name(),$vName,false);
                        });
                    });
                }
            }else if(method_exists($field,'items')){
                $vValue=$checkVal;
                if(!$isSourceData&&!is_null($checkVal)){//因为我可能强制设了 $vValue 为null,不让它显示
                    $fieldCopy=clone $field;
                    $vValue=$fieldCopy->setSave($data)->getSave();
                }


                //有值才显示
                if($vValue) {
                    $hideAllFieldArr=[];
                    $hideFieldArr=[];

                    foreach ($field->items() as $item){
                        if(!isset($item['hideFields'])){
                            continue;
                        }
                        $item['hideFields']->each(function(ModelField $hidelField)use($changeFieldHideList,$vName,$vType,$vValue,$arrHave,$item,$field,&$hideAllFieldArr,&$hideFieldArr){
                            $hideAllFieldArr[]=$hidelField->name();
                            //与JS中的一致
                            switch ($vType) {
                                case 'CheckboxField':
                                    $hide=$arrHave($vValue, $item['value']);
                                    break;
                                case 'SelectField':
                                    if ($field->multiple()) {
                                        $hide=$arrHave($vValue, $item['value']);
                                    } else {
                                        $hide=(string)$vValue === (string)$item['value'];
                                    }
                                    break;
                                default:
                                    $hide= (string)$vValue === (string)$item['value'];
                            }
                            if($hide){
                                $hideFieldArr[]=$hidelField->name();
                            }
                        });
                    }
                    foreach ($hideAllFieldArr as $fName){
                        //当是反转时，隐藏变为现实，显示变为隐藏，但不会对defHideAboutFields执行
                        $changeFieldHideList($fName,$vName,$reversalHideFields!==in_array($fName,$hideFieldArr));
                    }
                }else if(method_exists($field,'defHideAboutFields')&&$field->defHideAboutFields()){//默认隐藏所有
                    foreach ($field->items() as $item){
                        if(!isset($item['hideFields'])){
                            continue;
                        }
                        $item['hideFields']->each(function(ModelField $hidelField)use($changeFieldHideList,$vName){
                            $changeFieldHideList($hidelField->name(),$vName,true);
                        });
                    }
                }
            }


            //-----------------
            //隐藏(显示)其他相关字段
            $newHideFields=array_keys($fieldHideList);
            foreach ($oldHideFields as $f){
                if(in_array($f,$newHideFields)){
                    continue;
                }
                //重新显示的字段下面要重新判断
                $fieldInfo=$this->findByName($f);
                $checkHideField($fieldInfo,$data[$fieldInfo->name()]);
            }
            foreach ($newHideFields as $f){
                if(in_array($f,$oldHideFields)){
                    continue;
                }
                //新隐藏的字段
                $fieldInfo=$this->findByName($f);
                $checkHideField($fieldInfo,null);
            }
            //-------------------------
        };

        return $this->each(function(ModelField $v)use(&$fieldHideList,$data,&$checkHideField){
            $checkHideField($v,isset($fieldHideList[$v->name()])?null:($data[$v->name()]??null));
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

    /**
     * 用回调函数过滤数组中的元素
     * @access public
     * @param callable|null $callback 回调
     * @return static
     */
    public function filter(callable $callback = null)
    {
        $new=clone $this;
        if ($callback) {
            $new->items = $new->convertToArray(array_filter($new->items, $callback));
        }else{
            $new->items = $new->convertToArray(array_filter($new->items));
        }
        return $new;
    }
}