<?php


namespace tpScriptVueCurd;


use think\Collection;
use think\db\Query;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\field\CheckboxField;
use tpScriptVueCurd\field\ListField;
use tpScriptVueCurd\field\PasswordField;
use tpScriptVueCurd\field\SelectField;
use tpScriptVueCurd\field\StringField;
use tpScriptVueCurd\field\TreeSelectField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldNumHideField;
use tpScriptVueCurd\option\FieldNumHideFieldCollection;
use tpScriptVueCurd\option\grid\Grid;
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
    protected $gridByEdit=null;
    protected $gridByShow=null;

    public function __construct($items = [])
    {
        $this->setGroupItems($items);
        parent::__construct($items);
    }

    private function setGroupItems(array $items): void
    {
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

    /**
     * 设置字段到toArrayPageType
     * （调用toArray的时候，toArray自行根据此值判断处理，防止多余的数据到前台，影响性能）
     * @param string $toArrayPageType
     * @return $this
     */
    public function fieldToArrayPageType(string $toArrayPageType):self{
        $this->each(function (ModelField $v)use($toArrayPageType){$v->toArrayPageType=$toArrayPageType;});
        return $this;
    }


    public function toArray(): array{
        return array_map(static function ($value) {
            return $value instanceof ModelField ? $value->toArray() : $value;
        }, $this->items);
    }


    /**
     * 设置保存的值
     * @param array $data
     * @param BaseModel|null $old 原数据
     * @param bool $isExcelDo 是否EXCEL添加
     * @param FieldCollection|null $saveFields  更新保存的字段
     * @return $this
     * @throws \think\Exception
     */
    public function setSave(array $data,BaseModel $old,bool $isExcelDo=false,self &$saveFields=null): self
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


        //隐藏的选项不能被选中
       $this->each(function(ModelField $v)use($data,$old){
            if(!method_exists($v,'items')||empty($v->items())){
                return;
            }
            $items=[];
           foreach ($v instanceof TreeSelectField?$v->getSourceItems():$v->items() as $key=> $val){
                $items[$key]=$val;
                if(empty($val['showItemBy'])){
                    continue;
                }
                if(!$val['showItemBy']->check($data,true,$old)){
                    if(isset($data[$v->name()])&&$data[$v->name()]!==''){
                        if($v instanceof CheckboxField||(($v instanceof SelectField||$v instanceof TreeSelectField)&&$v->multiple())){
                            $valArr=is_array($data[$v->name()])?$data[$v->name()]:explode(',',$data[$v->name()]);
                            $data[$v->name()]=implode(',',array_filter($valArr, static fn($vv)=>(string)$vv!==(string)$val['value']));
                        }else if((string)$data[$v->name()]===(string)$val['value']){
                            $data[$v->name()]='';
                        }
                    }
                    unset($items[$key]);
                }
            }
           $v->items($items);
        });


        $notNullNames=null;
        if($this->getSaveHideFieldSetNull()){
            //隐藏的字符串要设置为空
            $notNullNames=$this->filterHideFieldsByData($data,$old,false)->column('name');
            $fields=$this;
        }else{
            $fields=$this->filterHideFieldsByData($data,$old,false);
        }
        $fields->each(function(ModelField $v)use($data,$notNullNames,$old){
            $v->setAttrValByWheres($data,false,$old);
            try{
                if(is_null($notNullNames)||in_array($v->name(),$notNullNames,true)){
                    $v->setSave($data,$old);
                }else{
                    $v->required(false);
                    $v->setSaveToNull();
                }
            }catch (\Exception $e){
                throw new \think\Exception($v->title().'：'.$e->getMessage());
            }
        });

        $saveFields=$fields->filter(fn($v)=>is_null($notNullNames)||in_array($v->name(),$notNullNames,true));

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
     * @param bool $notFoundErrors
     * @return ModelField
     * @throws \think\Exception
     */
    public function findByName(string $name,bool $notFoundErrors=true):?ModelField{
        foreach ($this->items as $item) {
            if($item->name()===$name){
                return $item;
            }
        }
        if($notFoundErrors){
            throw new \think\Exception('未找到字段:'.$name);
        }
        return null;
    }

    /**
     * 根据字段name获取字段信息
     * @param string $name
     * @param bool $notFoundErrors
     * @return ModelField|null
     * @throws \think\Exception
     */
    public function getFieldByNmae(string $name,bool $notFoundErrors=true):?ModelField{
        return $this->findByName($name,$notFoundErrors);
    }



    /**
     * 处理要显示的数据
     * @param array $data
     */
    public function doShowData(array &$data): void
    {
        $this->each(function(ModelField $v)use(&$data){
            if($v instanceof PasswordField||($v instanceof StringField&&$v->disengageSensitivity())||!isset($data[$v->name()])){
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
     * @param BaseModel $sourceData
     * @return $this
     */
    public function filterHideFieldsByShow(BaseModel $sourceData):self{
        return $this->filterHideFieldsByData($sourceData->toArray(),$sourceData,true);
    }

    /**
     * 查看页面，显示的字段处理
     * @param BaseModel $sourceData
     * @return $this
     */
    public function whenShowSetAttrValByWheres(BaseModel $sourceData):self{
        $this->each(function (ModelField $v)use($sourceData){
            $v->setAttrValByWheres($sourceData->toArray(),true,$sourceData);
        });
        return $this;
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
     * 获取查询数据条件
     * @param array|null $myFilter
     * @return array
     */
    private function getFilterWhereData(array $myFilter=null):array{
        if(is_null($myFilter)){
            $myFilter=$this->getParamFilterData();
        }
//        if(is_string($myFilter)){
//            $myFilter=json_decode($myFilter,true);
//        }
        if(empty($myFilter)){
            return [];
        }
        return $myFilter;
    }


    /**
     * 根据参数获取 查询信息
     * @return array
     */
    public function getParamFilterData():array{
        $myFilter=input('filterData',null,null);
        if(empty($myFilter)){
            //支持下划线写法
            $myFilter=input('filter_data',null,null);
            if(empty($myFilter)){
                return [];
            }
        }
        if($myFilter&&is_string($myFilter)){
            $myFilter=json_decode($myFilter,true);
        }

        return $myFilter?:[];
    }

    /**
     * 生成查询条件
     * @param null|string|array $myFilter 传入参数和自己获取两种方法(json或数组或空)
     * @return array|\Closure
     */
    public function getFilterWhere(array $myFilter=null){
        return function(Query $query)use($myFilter){
            $myFilter=$this->getFilterWhereData($myFilter);

            FieldDo::doIndexFilterBefore($this,$query,$myFilter);


            if(empty($myFilter)){
                return;
            }


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
     * @param array $data       用户提交上来的数据
     * @param BaseModel|null $old        原数据
     * @param bool $isSourceData
     * @return $this
     */
    private function filterHideFieldsByData(array $data, BaseModel $old, bool $isSourceData=true):self{
        $fieldHideList=$this->getFiledHideListByData($data,$old,$isSourceData);
        if(empty($fieldHideList)){
            return $this;
        }
        return $this->filter(function(ModelField $v)use($fieldHideList){
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
     * 获取显示中将会隐藏的字段信息
     * @param BaseModel $info
     * @return array
     */
    public function getFiledHideList(BaseModel $info):array{
        return $this->getFiledHideListByData($info->toArray(),$info,true);
    }

    /**
     * 获取将会隐藏的字段信息
     * @param array $data
     * @param BaseModel|null $old 原数据
     * @param bool $isSourceData
     * @return array
     */
    private function getFiledHideListByData(array $data, BaseModel $old, bool $isSourceData=true):array{
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


        $checkHideField=function(ModelField $field,$checkVal)use($arrHave,&$data,&$changeFieldHideList,$isSourceData,&$checkHideField,&$fieldHideList,$old){
            $hideSelf=$field->hideSelf();
            if($hideSelf){
                $changeFieldHideList($field->name(),implode(',',$hideSelf->getAboutFields()),$hideSelf->check($data,$isSourceData,$old));
            }


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
                    try{
                        $vValue=$fieldCopy->required(false)->setSave($data,$old)->getSave();
                    }catch (\Exception $exception){
                        throw new \think\Exception($fieldCopy->title().'：'.$exception->getMessage());
                    }

                }
                //有值才显示
                if(!is_null($vValue)&&$vValue!==''){
                    //当是反转时，隐藏变为现实，显示变为隐藏，但不会对defHideAboutFields执行

                    $allFields=[];
                    $hideFArr=[];
                    $hideFields->each(function(FieldNumHideField $v)use($vValue,&$allFields,&$hideFArr){
                        $isHide=FieldNumHideFieldCollection::checkValueInBetween($vValue,$v,true);
                        $v->getFields()->each(function(ModelField $f)use(&$allFields,&$hideFArr,$isHide){
                            in_array($f->name(),$allFields,true)||$allFields[]=$f->name();
                            if($isHide){
                                in_array($f->name(),$hideFArr,true)||$hideFArr[]=$f->name();
                            }
                        });
                    });

                    foreach ($allFields as $v){
                        $changeFieldHideList($v,$vName,$reversalHideFields!==in_array($v,$hideFArr));
                    }
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
                    try{
                        $vValue=$fieldCopy->required(false)->setSave($data,$old)->getSave();
                    }catch (\Exception $exception){
                        throw new \think\Exception($fieldCopy->title().'：'.$exception->getMessage());
                    }
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

        $this->each(function(ModelField $v)use(&$fieldHideList,$data,&$checkHideField){
            $checkHideField($v,isset($fieldHideList[$v->name()])?null:($data[$v->name()]??null));
        });

        return $fieldHideList;
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
            $tpl=$field->componentTpls();
            isset($tpl->$type)&&$return[$field->name()]=$tpl->toArray($tpl->$type);
            $otherComponentJsFields=$field->getOtherComponentJsFields();
            if($otherComponentJsFields&&!$otherComponentJsFields->isEmpty()){
                foreach ($otherComponentJsFields->getComponents($type) as $k => $v) {
                    $return[$field->name() . '[' . $k . ']'] = $v;
                }
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
        $new->items=array_values($new->items);
        return $new;
    }


    /**
     * 过滤掉非导入的字段
     * @return $this
     */
    public function excelFilter():self{
        return $this->filter(function(ModelField $v){
            if(!$v->canExcelImport()){
                return false;
            }
            if($v->editShow()){
                return true;
            }
            return false;
        });
    }

    /**
     * 详情与编辑页面布局
     * @param callable $editOrShowGrid
     * @param callable|null $showGrid  当为空时，使用编辑的页面布局
     * @return $this
     */
    public function gridBy(callable $editOrShowGrid,?callable $showGrid=null){
        /*$editOrShowGrid=function ($info,?BaseModel $base,array $fields,string $group){
            return
        }*/
        $this->gridByEdit=$editOrShowGrid;
        if(is_null($showGrid)){
            $this->gridByShow=$editOrShowGrid;
        }else{
            $this->gridByShow=$showGrid;
        }
        return $this;
    }
    public function getEditGridBy():?callable{
        return $this->gridByEdit;
    }
    public function getShowGridBy():?callable{
        return $this->gridByShow;
    }

}