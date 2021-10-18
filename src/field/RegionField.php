<?php


namespace tpScriptVueCurd\field;

use app\common\constants\RegionConstant;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\RegionFilter;
use tpScriptVueCurd\ModelField;


use app\admin\model\SystemRegion;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\NumHideFields;

/**
 * 地区
 * Class RegionField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class RegionField extends ModelField
{

    use NumHideFields;
    /**
     * 地区树型
     * @var array
     */
    protected array $regionTree = [];
    protected string $defaultFilterClass = RegionFilter::class;
    protected bool $canEdit=false;//编辑页面是否可修改
    protected bool $canCheckParent=false;//是否可选中父级
    protected bool $multiple=false;//是否多选

    protected ?self $parentField = null;//父字段
    protected ?self $childField = null;//子字段

    protected $nullVal=0;//字段在数据库中为空时的值

    private bool $is_to_arr_do=false;




    public function __construct($otherConfig)
    {
        $this->regionTree = $otherConfig?:SystemRegion::getMyCascaderData();
        $this->listColumnWidth(90);
        parent::__construct();
    }

    /**
     * 父地区字段
     * @param RegionField|null $parentField
     * @return RegionField
     */
    public function parentField(self $parentField=null): ?RegionField
    {
        if(!is_null($parentField)){
            $parentField->filter(new \tpScriptVueCurd\filter\EmptyFilter());
            $parentField->pushFieldDo()->setEditShowDo(function (BaseModel &$info,?BaseModel $base,ModelField $field,bool $isStepNext){
                $field->editShow(false);
            });
        }
        return $this->doAttr('parentField', $parentField);
    }

    /**
     * 子地区字段
     * @param RegionField|null $childField
     * @return RegionField
     */
    public function childField(self $childField=null): ?RegionField
    {
        return $this->doAttr('childField', $childField);
    }


    /**父字段名
     * @param bool|null $canCheckParent
     * @return $this|string
     */
    public function canCheckParent(bool $canCheckParent = null)
    {
        return $this->doAttr('canCheckParent', $canCheckParent);
    }


    /**
     * 是否可多选
     * @param bool $multiple
     * @return RegionField|bool
     */
    public function multiple(bool $multiple=null){
        return $this->doAttr('multiple',$multiple);
    }



    /**
     * 编辑页面是否可修改
     * @param bool|null $canEdit
     * @return RegionField|bool
     */
    public function canEdit(bool $canEdit=null){
        if(!is_null($canEdit)){
            $this->pushFieldDo()->setEditShowDo(function (BaseModel &$info,?BaseModel $base,ModelField $field,bool $isStepNext)use($canEdit){
                foreach ($field->getAboutRegions('child') as $v){
                    $v->canEdit($canEdit);
                }
            })->setSaveBeforeDo(function (array &$postData,BaseModel $before,?BaseModel $base,ModelField $field)use($canEdit){
                foreach ($field->getAboutRegions('child') as $v){
                    $v->canEdit($canEdit);
                }
            });
        }
        return $this->doAttr('canEdit',$canEdit);
    }


    /**
     * 设置保存的值
     * @param array $data 数据值集合
     * @param BaseModel $old
     * @return $this
     * @throws \think\Exception
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        if($this->multiple){
            $name = $this->name();
            if (isset($data[$name])) {
                $val = $data[$name];
                if (is_array($val)) {
                    foreach ($val as $v){
                        $this->checkValIsCheckParentErr($v);
                    }
                    $this->save = implode(',',$val);
                } else {
                    $this->checkValIsCheckParentErr($val);
                    $this->save = $val;
                }
            } else {
                $this->save = $this->nullVal();
            }

        }else{
            $name = $this->name();
            if (isset($data[$name])) {
                $val = $data[$name];
                is_array($val)||$val=explode(',',$val);
                if(count($val)===1){
                    if(empty(current($val))){
                        $this->save = $this->nullVal();
                    }else{
                        $this->save = current($val);
                        $this->checkValIsCheckParentErr($this->save);
                    }

                }else{
                    $regions=$this->getAboutRegions();
                    foreach ($regions as $k=>$v){
                        $data[$v->name()]=$val[count($val)-count($regions)+$k]??0;
                        $v->save= $data[$v->name()];
                    }
                    if(!isset($this->save)||empty($this->save)){
                        $this->save = $this->nullVal();
                    }
                    $this->checkValIsCheckParentErr($this->save);
                }
            } else {
                $this->save = $this->nullVal();
            }
        }
        $this->defaultCheckRequired($this->save);
        return $this;
    }



    /**
     *
     * 显示时要处理的数据
     * @param array $dataBaseData 从数据库中获取的数据
     */
    public function doShowData(array &$dataBaseData): void
    {
        $name = $this->name();
        if (isset($dataBaseData[$name])) {
            if($this->multiple){
                if(!is_numeric(str_replace(',','',$dataBaseData[$name]))){
                    return;
                }
            }else{
                if(!is_numeric($dataBaseData[$name])){
                    return;
                }
            }

            if( empty($dataBaseData[$name])){
                $dataBaseData[$name]=$this->getTreeToList()[$dataBaseData[$name]]['label']??'';
                return;
            }

            if(!$this->multiple){
                $dataBaseData[$name] = $this->getTreeToList()[$dataBaseData[$name]]['label']??$this->getRegionName($dataBaseData[$name]);
                return;
            }

            $arr=is_array($dataBaseData[$name])?$dataBaseData[$name]:explode(',',$dataBaseData[$name]);

            foreach ($arr as $k=>$v){
                $arr[$k]=$this->getRegionName($v)?:$v;
            }
            $dataBaseData[$name]=implode('，',$arr);
        }
    }


    /**
     * 获取当前选项的列表
     * @return array
     */
    public function getTreeToList():array{
        static $list=[];
        $func=function($tree,$pid=null)use(&$list,&$func){
            foreach ($tree as $v){
                if(!isset($v['pid'])){
                    $v['pid']=is_null($pid)?'':(string)$pid;
                }
                isset($list[$this->guid()])||$list[$this->guid()]=[];
                $list[$this->guid()][$v['value']]=$v;
                if(!empty($v['children'])){
                    $func($v['children'],$v['value']);
                }
            }
        };
        if(!isset($list[$this->guid()])){
            $func($this->regionTree);
        }
        return $list[$this->guid()]??[];
    }


    /** 可以是 桐君街道 或 阆苑村 或 桐君街道-阆苑村
     * @param string $region_name
     * @return array|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getRegionByName(string $region_name): ?array
    {
        static $regions = null;
        if (is_null($regions)) {
            $level=count($this->getAboutRegions());

            $childs=[];
            $infos=[];
            foreach (SystemRegion::getAll() as $v) {
                isset($childs[$v['pid']])||$childs[$v['pid']]=[];
                $childs[$v['pid']][]=$v;
                $infos[$v['id']]=$v;
            }

            $regions=[];
            if($level>1){
                $titles=function ($info,$lv,$titleArr)use($level,$childs,&$titles,&$regions){
                    $titleArr[]=$info['name'];
                    if($level>$lv&&isset($childs[$info['id']])){
                        foreach ($childs[$info['id']] as $val){
                            $titles($val,$lv+1,$titleArr);
                        }
                    }elseif($level===$lv){
                        $regions[implode('-',$titleArr)]=$info;
                    }
                };
                foreach ($childs as $k=>$v){
                    isset($infos[$k])&&$titles($infos[$k],1,[]);
                }
            }else{
                foreach (SystemRegion::getAll() as $v) {
                    $regions[$v['name']]=$v;
                }
            }
        }
        return $regions[$region_name] ?? null;
    }


    public function getRegionName(int $id):string{
        static $regionNames=null;
        if(is_null($regionNames)){
            $regionNames=[];
            foreach (SystemRegion::getAll() as $v){
                $regionNames[$v['id']]=$v['name'];
            }
        }
        return $regionNames[$id]??'';
    }


    public function getRegionTree(): array
    {
        return $this->regionTree;
    }

    public function setRegionTree(array $RegionTree):self{
        $this->regionTree=$RegionTree;
        return $this;
    }


    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->explain = "填入：地区名称,需和\n" . url('index/showRegionPage', [], true, true)->build() . "\n中的名称对应";
        $excelFieldTpl->width = 40;
        $excelFieldTpl->wrapText = true;
    }


    /**
     * EXCEL导入时，对数据的处理
     * @param array $save
     * @throws \think\Exception
     */
    public function excelSaveDoData(array &$save): void
    {
        $regions=$this->getAboutRegions();
        if(!isset($save[$this->name()])){
            return;
        }

        if (!is_numeric($save[$this->name()])) {
            $titles=[];
            foreach ($regions as $v){
                if(!isset($save[$v->name()])){
                    throw new \think\Exception('缺少'.$v->title());
                }
                $titles[]=$save[$v->name()];
            }
            $lastRegionInfo= $this->getRegionByName(implode('-',$titles));
            if(empty($lastRegionInfo)){
                throw new \think\Exception('未找到相关地区[ '.implode('-',$titles).' ]');
            }
            $ids=explode(',',$lastRegionInfo['pids']);
            $ids[]=$lastRegionInfo['id'];

            $idsI=count($ids)-count($regions);
            foreach ($regions as $v){
                $save[$v->name()]=$ids[$idsI];
                $idsI++;
            }
        }else{
            if(empty($save[$this->name()])){
                return;
            }
            $list=$this->getTreeToList();
            if(!isset($list[$save[$this->name()]])){
                throw new \think\Exception('地区值不正确');
            }
            $info=$list[$save[$this->name()]];

            $ids=explode(',',$info['pids']);
            $ids[]=$info['id'];

            $idsI=count($ids)-count($regions);
            foreach ($regions as $v){
                if(!isset($save[$v->name()])||(int)$save[$v->name()]!==(int)$ids[$idsI]){
                    throw new \think\Exception('地区值不正确（'.$v->title().':'.$save[$v->name()].'）');
                }
                if($v->guid()===$this->guid()){
                    break;
                }
                $idsI++;
            }
        }
    }


    /**
     * 获取所有的父地区-自己-子地区
     * @return self[]|RegionField[]
     */
    public function getAboutRegions(string $thisIsParent=''){
        static $return=[];
        if(!isset($return[$this->guid()],$return[$this->guid()][$thisIsParent])){
            $getRegions=function (self $field,string $thisIsParent='')use(&$getRegions){
                $ps=[];
                $cs=[];
                if($thisIsParent!=='parent'&&$field->parentField()){
                    $ps=$getRegions($field->parentField(),'child');
                    $ps[]=$field->parentField();
                }
                if($thisIsParent!=='child'&&$field->childField()){
                    $cs=$getRegions($field->childField(),'parent');
                    $cs[]=$field->childField();
                }
                return [
                    ... $ps,
                    ...($thisIsParent?[]:[$field]),
                    ...  $cs,
                ];
            };
            isset($return[$this->guid()])||$return[$this->guid()]=[];
            $return[$this->guid()][$thisIsParent]=$getRegions($this,$thisIsParent);
        }
        return $return[$this->guid()][$thisIsParent];
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tp-script-vue-curd-static.php?field/region/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        if($this->multiple){
            $option->setTypeText();
        }else{
            $option->setTypeInt();
        }
    }

    private function checkValIsCheckParentErr(int $val):void{
        if(empty($val)||$this->canCheckParent()){
            return;
        }
        $regions=$this->getAboutRegions();
        foreach ($regions as $k=>$v){
            if($v->guid()===$this->guid()&&isset($regions[$k+1])){
                return ;
            }
        }

        $list= $this->getTreeToList();
        if(!isset($list[$val])){
            throw new \think\Exception('值'.$val.'不可选中');
        }
        if(!empty($list[$val]['children'])){
            throw new \think\Exception('不可选中父级['.$list[$val]['name'].']');
        }
    }

    /**
     * 多级初始化
     * @param ...$regions
     * @return self[]
     */
    public static function tree(...$regions):array{
        /**
         * @var self[] $regions
         */
        foreach ($regions as $k=>$v){
            if($k>0){
                $v->parentField($regions[$k-1]);
            }
            if(isset($regions[$k+1])){
                $v->childField($regions[$k+1]);
            }
        }
        return $regions;
    }

    public function toArray(): array
    {
        $aboutRegions=[];
        if($this->is_to_arr_do!==true){
            foreach ($this->getAboutRegions() as $v){
                $v->is_to_arr_do=true;
                $v->objWellToArr=false;
                $aboutRegions[]=$v->toArray();

            }
        }
        $data=parent::toArray();
        $data['aboutRegions']=$aboutRegions;
        return $data;
    }

}