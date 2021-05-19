<?php


namespace tpScriptVueCurd;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\field\StringField;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;
use tpScriptVueCurd\option\FieldStepCollection;
use tpScriptVueCurd\option\FieldWhere;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\traits\Func;

/**
 * Class ModelField
 * @package tpScriptVueCurd
 * @author tj 1079798840@qq.com
 */
abstract class ModelField
{
    use Func;

    protected string $guid='';
    protected string $name='';//字段名
    protected array $tag=[];//标签
    protected string $group='';//分组
    protected string $title='';//标题
    protected string $ext='';//后缀
    protected string $placeholder='';//输入提示
    protected string $editExplain='';//字段编辑时下方的说明
    protected bool $listShow=false;//是否在列表中显示
    protected int $listColumnWidth=0;//指定列宽（0，不指定）
    protected bool $listSort=true;//列表中时候可排序
    protected string $listFixed='';//列表中，列是否浮动，'left'/'right'
    protected bool $required=false;//字段是否必填
    protected bool $readOnly=false;//找到是否只读
    protected bool $editShow=true;//字段在添加修改时是否显示
    protected bool $showPage=true;//字段在详情页面中是否显示
    protected string $type='';//字段类型，不可修改
    protected $save;//提交保存时，要提交的数据库的值
    protected ?ModelFilter $filter=null;//筛选对象
    protected string $defaultFilterClass='';//筛选类型
    protected bool $pub=false;//是否在前台公开
    protected bool $canExcelImport=true;//是否可以excel导入
    protected ?array $editLabelCol=null;//编辑界面 label布局
    protected string $editLabelAlign='right';//编辑界面 label对齐
    protected ?array $editWrapperCol=null;//编辑界面 为输入控件设置布局样式 用法同 editLabelCol
    protected bool $editColon=true;//是否显示label后面的冒号
    protected bool $showUseComponent=false;//查看页面这一行，完全使用组件自己的显示（不显示左边的标题）
    protected $validateRule=null;//数据验证
    protected $nullVal='';//字段在数据库中为空时的值
    /**
     * @var FieldDo[] $fieldDoList
     */
    protected array $fieldDoList=[];//数据列表时执行，数据显示时执行（方便一些数据处理，也可以叫字段钩子）
    protected ?FieldWhere $hideSelf=null;//编辑时是否隐藏本字段
    public const REQUIRED=true;//开启必填验证
    public bool $objWellToArr=true;


    public function __construct(){
        $this->guid=create_guid();
        $this->type=class_basename(static::class);
        if($this->defaultFilterClass){
            $this->filter(new $this->defaultFilterClass($this));
        }
    }

    /**
     * 初始化子类
     * @param string|null $name
     * @param string|null $title
     * @param string|null $group
     * @return $this
     */
    public static function init(string $name=null,string $title=null,string $group=null,$otherConfig=null): self
    {
        $obj=new static($otherConfig);
        is_null($name)||$obj->name($name);
        is_null($title)||$obj->title($title);
        is_null($group)||$obj->group($group);
        return $obj;
    }

    public function getType():string{
        return $this->type;
    }

    /**字段名
     * @param string|null $name
     * @return $this|string
     */
    public function name(string $name=null){
        return $this->doAttr('name',$name);
    }

    /**字段标题
     * @param string|null $title
     * @return $this|string
     */
    public function title(string $title=null){
        return $this->doAttr('title',$title);
    }

    /**分组
     * @param string|null $group
     * @return $this|string
     */
    public function group(string $group=null){
        return $this->doAttr('group',$group);
    }

    /**标签（多个）
     * @param string|null $tag
     * @return $this|array
     */
    public function tag(string $tag=null){
        if(is_null($tag)){
            return $this->tag;
        }
        $this->tag[]=$tag;
        return $this;
    }

    /**
     * 要删除的标签
     * @param array $tags
     * @return $this
     */
    public function removeTag(array $tags):self{
        $this->tag=array_diff($this->tag,$tags);
        return $this;
    }


    /**
     * 清空标签
     * @return $this
     */
    public function clearTag():self{
        $this->tag=[];
        return $this;
    }

    /**结尾
     * @param string|null $ext
     * @return $this|string
     */
    public function ext(string $ext=null){
        return $this->doAttr('ext',$ext);
    }

    /**输入提示
     * @param string|null $placeholder
     * @return $this|string
     */
    public function placeholder(string $placeholder=null){
        return $this->doAttr('placeholder',$placeholder);
    }

    /**字段编辑时下方的说明
     * @param string|null $editExplain
     * @return $this|string
     */
    public function editExplain(string $editExplain=null){
        return $this->doAttr('editExplain',$editExplain);
    }


    /**
     * 是否在后台列表中显示出来
     * @param bool|null $listShow
     * @return $this|bool
     */
    public function listShow(bool $listShow=null){
        return $this->doAttr('listShow',$listShow);
    }

    /**列表中，列是否浮动，'left'/'right'
     * @param string|null $listFixed
     * @return $this|string
     */
    public function listFixed(string $listFixed=null){
        return $this->doAttr('listFixed',$listFixed);
    }



    /**
     * 列宽度
     * @param int|null $listColumnWidth
     * @return $this|bool
     */
    public function listColumnWidth(int $listColumnWidth=null){
        return $this->doAttr('listColumnWidth',$listColumnWidth);
    }

    /**
     * 列表中时候可排序
     * @param bool|null $listSort
     * @return $this|bool
     */
    public function listSort(bool $listSort=null){
        return $this->doAttr('listSort',$listSort);
    }

    /**
     * 是否必填
     * @param bool|null $required
     * @return $this|bool
     */
    public function required(bool $required=null){
        if(is_null($required)){
            return self::REQUIRED&&$this->required;
        }
        $this->required=$required;
        return $this;
    }
    /**
     * 字段是否只读
     * @param bool|null $readOnly
     * @return $this|bool
     */
    public function readOnly(bool $readOnly=null){
        if(is_null($readOnly)){
            return $this->readOnly;
        }
        $this->readOnly=$readOnly;
        return $this;
    }
    /**
     * 是否在前端公开
     * @param bool|null $pub
     * @return $this|bool
     */
    public function pub(bool $pub=null){
        return $this->doAttr('pub',$pub);
    }
    /**
     * 是否可以excel模板导入
     * @param bool|null $canExcelImport
     * @return $this|bool
     */
    public function canExcelImport(bool $canExcelImport=null){
        return $this->doAttr('canExcelImport',$canExcelImport);
    }

    /**
     * 字段在添加修改时是否显示
     * @param bool|null $editShow
     * @return $this|bool
     */
    public function editShow(bool $editShow=null){
        return $this->doAttr('editShow',$editShow);
    }


    /**
     * 字段在详情页面中是否显示
     * @param bool|null $showPage
     * @return $this|bool
     */
    public function showPage(bool $showPage=null){
        return $this->doAttr('showPage',$showPage);
    }

    /**
     * 编辑界面 label布局
     * @param array|null $editLabelCol
     * @param bool $forceSet  是否强制设置值，当要设置值为null时使用
     * @return $this|array|null
     */
    public function editLabelCol(array $editLabelCol=null,bool $forceSet=false){
        if($forceSet){
            $this->editLabelCol=$editLabelCol;
            return $this;
        }
        return $this->doAttr('editLabelCol',$editLabelCol);
    }

    /**
     * 标签文本对齐方式
     * @param string|null $editLabelAlign
     * @return $this|string
     */
    public function editLabelAlign(string $editLabelAlign=null){
        return $this->doAttr('editLabelAlign',$editLabelAlign);
    }

    /**
     * 编辑界面 为输入控件设置布局样式 用法同 editLabelCol
     * @param array|null $editWrapperCol
     * @param bool $forceSet  是否强制设置值，当要设置值为null时使用
     * @return $this|array|null
     */
    public function editWrapperCol(array $editWrapperCol=null,bool $forceSet=false){
        if($forceSet){
            $this->editWrapperCol=$editWrapperCol;
            return $this;
        }
        return $this->doAttr('editWrapperCol',$editWrapperCol);
    }


    /**
     * 查看页面这一行，完全使用组件自己的显示（不显示左边的标题）
     * @param bool|null $showUseComponent
     * @return $this|bool
     */
    public function showUseComponent(bool $showUseComponent=null){
        return $this->doAttr('showUseComponent',$showUseComponent);
    }

    /**
     * 是否显示label后面的冒号
     * @param bool|null $editColon
     * @return $this|bool
     */
    public function editColon(bool $editColon=null){
        return $this->doAttr('editColon',$editColon);
    }


    /**
     * 字段在添加修改时是否显示
     * @param $validateRule
     * @return $this|mixed
     */
    public function validateRule($validateRule=null){
        return $this->doAttr('validateRule',$validateRule);
    }


    /**
     * 隐藏本字段条件
     * @param FieldWhere|null $hideSelf
     * @return $this|FieldWhere
     */
//    public function hideSelf(FieldWhere $hideSelf=null){
//        return $this->doAttr('hideSelf',$hideSelf);
//    }
    /**
     * 隐藏本字段条件，不能修改，修改请使用pushHideSelfWhere
     * @return FieldWhere|null
     */
    public function hideSelf():?FieldWhere{
        return $this->hideSelf??null;
    }

    /**
     * 新增隐藏本字段条件
     * @param FieldWhere $where
     * @return $this
     */
    public function pushHideSelfWhere(FieldWhere $where):self{
        if(!$this->hideSelf()){
            $this->hideSelf=FieldWhere::make(StringField::init(FieldWhere::RETURN_FALSE_FIELD_NAME),FieldWhere::RETURN_FALSE_FIELD_NAME.'[这个条件是初始化条件，不用管]');
        }
        $this->hideSelf->or($where);
        return $this;
    }
    

    /**
     * 自定义其他属性
     * @param $name
     * @param null $value
     * @return $this|null|mixed
     * @throws \think\Exception
     */
    public function other($name, $value=null)
    {
        if(method_exists($this,$name)){
            throw new \think\Exception('[ '.$name.' ]方法在'.(self::class).'中已存在，请直接调用，或更换属性名称');
        }
        if(is_null($value)){
            return $this->$name??null;
        }
        $this->$name=$value;
        return $this;
    }



    protected function doAttr($name,$value)
    {
        if(is_null($value)){
            return $this->$name;
        }
        $this->$name=$value;
        return $this;
    }

    public function toArray(): array
    {
        $data=[];
        foreach (get_class_vars(static::class) as $k=>$v){
            if(method_exists($this,$k)){
                $data[$k]=$this->$k();
            }elseif (!isset($this->$k)){
                continue;
            }else{
                $data[$k]=$this->$k;
            }
            if(is_object($data[$k])&&method_exists($data[$k],'toArray')){
                if($this->objWellToArr){
                    $data[$k]=$data[$k]->toArray();
                }else{
                    unset($data[$k]);
                }
            }
        }
        return $data;
    }


    /**
     * 设置保存的值，子类不能重写
     * @param array $data  数据值集合
     * @return $this
     */
    final public function setSave(array $data): self
    {
        $this->setSaveVal($data);
        if(isset($this->save)){
            $checkData=$data;
            $checkData[$this->name()]=$this->save;
            $this->validate($checkData,false);
        }
        return $this;
    }


    /**
     * 设置为空值（子字段有需要的话，继承重写）
     * @return $this
     * @throws \think\Exception
     */
    public function setSaveToNull():self{
        $nullVal=$this->nullVal();
        $this->defaultCheckRequired($nullVal);
        $this->save=$nullVal;
        return $this;
    }

    /**
     * 字段为空时的值
     * @return mixed
     */
    public function nullVal(){
        return $this->nullVal;
    }

    /**
     * 设置字段在数据库中为空时的值
     * @param $nullVal
     * @return $this
     */
    public function setNullVal($nullVal):self{
        $this->nullVal=$nullVal;
        return $this;
    }

    /**
     * 设置保存的值，子类可重写
     * @param array $data
     * @return $this
     * @throws \think\Exception
     */
    protected function setSaveVal(array $data):self{
        if(isset($data[$this->name()])){
            $this->defaultCheckRequired($data[$this->name()]);
            $this->save=$data[$this->name()];
        }
        return $this;
    }


    public function getSave(){
        return $this->save;
    }


    /**
     * 执行数据验证，如果有验证规则的话
     * @param array $data
     * @param bool $throwTitle
     * @throws \think\Exception
     */
    final public function validate(array $data,bool $throwTitle):void{
        if(!$this->validateRule()){
            return;
        }

        $title='';
        if($throwTitle){
            if($this->title()){
                $title='|'.$this->title();
            }
        }else{
            $title='|ERROR_TITLE';
        }

        $validate=\think\facade\Validate::rule($this->name().$title, $this->validateRule());
        if(!$validate->check($data)){
            throw new \think\Exception(str_replace('ERROR_TITLE','',$validate->getError()));
        }
    }


    /**
     *
     * 显示时要处理的数据
     * @param array $dataBaseData  从数据库中获取的数据
     */
    public function doShowData(array &$dataBaseData): void{}


    /**
     * 设置筛选，如果要不启用筛选，传入参数 new emptyFilter
     * @param ModelFilter|null $filter
     * @return $this|ModelFilter
     */
    public function filter(ModelFilter $filter=null){
        if($filter&&get_class($filter)===EmptyFilter::class){
            $this->filter=null;
            return $this;
        }
        return $this->doAttr('filter',$filter);
    }


    /**
     * 处理筛选(可调用filter对象的一些方法) ->doFilter(function($filter){ $filter->setItems(); })
     * @param $func
     * @return $this
     */
    public function doFilter($func):self{
        $filter=$this->filter();
        if(is_null($filter)){
            return $this;
        }
        $func($filter);
        return $this;
    }


    /**
     * 设置当前字段在列表中默认不显示筛选
     * @return $this
     */
    public function setDefaultHideFilter():self{
        $this->doFilter(fn(ModelFilter $filter)=>$filter->setShow(false));
        return $this;
    }


    /**
     * 模板导入时备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    abstract public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void;


    /**
     * EXCEL导入时，对数据的处理（之后再执行setSave）
     * @param array $save
     */
    public function excelSaveDoData(array &$save):void{
        //默认不用处理
    }



    /**
     * 默认是否为空判断，在 setSave 里面调用
     * @param $val
     * @param string $msg
     * @throws \think\Exception
     */
    final protected function defaultCheckRequired($val,string $msg='不可为空'):void{
        if($this->required()&&empty($val)){
            throw new \think\Exception($msg);
        }
    }


    /**
     * 字段模板配置
     * @return FieldTpl
     */
    abstract public static function componentUrl():FieldTpl;


    protected FieldStepCollection $steps;

    /**
     * 字段所属步奏配置
     * @param null $steps
     * @return $this|FieldStepCollection|null
     * @throws \think\Exception
     */
    public function steps($steps=null){
        if(is_null($steps)){
            return $this->steps??null;
        }

        if(is_array($steps)){
            $stepList=FieldStepCollection::make();
            foreach ($steps as $v){
                $stepList->push(clone $v);
                $v->removeFieldData();
            }
        }else if($steps instanceof FieldStep){
            $stepList=FieldStepCollection::make([clone $steps]);
            $steps->removeFieldData();
        }else{
            throw new \think\Exception($this->name().' 的steps设置类型错误');
        }


        $stepList=$stepList->map(function(FieldStep $val){
            $val->setFieldName($this->name());
            return $val;
        });
        $this->steps=$stepList;
        return $this;
    }


    /**
     * 设置 steps 和group,如果没有传入group，group为步骤标题
     * @param $step
     * @param string|null $group
     * @return $this
     * @throws \think\Exception
     */
    public function setStepsAndGroup($step,string $group=null): self
    {
        $this->steps($step);
        if(is_null($group)){
            $group=$this->steps()[0]->getTitle();
        }
        $this->group($group);
        return $this;
    }


    /**
     * 如果传入有值，返回$this；如果传入无值，会在fieldDoList中新建一个并返回新建的FieldDo
     * @param FieldDo|null $fieldDo
     * @return $this|FieldDo
     */
    public function pushFieldDo(FieldDo $fieldDo=null){
        if(!is_null($fieldDo)){
            $this->fieldDoList[]=$fieldDo;
            return $this;
        }
        $fieldDo=new FieldDo;
        $this->fieldDoList[]=$fieldDo;
        return $fieldDo;
    }


    /**
     * 获取FieldDo列表
     * @return FieldDo[]
     */
    public function getFieldDoList():array{
        return $this->fieldDoList;
    }

    public function guid(): string
    {
        return $this->guid;
    }


    /**
     * 复制字段
     * @param null|bool|string $newName  新的字段名，如果为null或false，表示使用老的字段名（不更改）。如果是true表示知道生成。如果是string，表示为新的字段名
     * @param bool $saveSetOldName         是否 保存数据库时，使用的字段名称为老的字段的名称
     * @return $this
     */
    public function cloneField($newName=null,bool $saveSetOldName=true):self{
        $field=clone $this;
        if($newName===false||is_null($newName)){
            return $field;
        }
        if($newName===true){
            $newName='__CLONE__'.$newName;
        }

        $oldName=$field->name();
        $field->name($newName);

        //克隆的字段在数据库中有克隆的字段的字段名
        if(!$saveSetOldName){
            return $field;
        }

        $field->pushFieldDo()
            ->setEditShowDo(static function(...$params)use($oldName){
                $params['field']->name($oldName);
            })
            ->setSaveBeforeDo(function(...$params)use($oldName){
                $params['field']->name($oldName);
            })->setShowInfoBeforeDo(function(&...$params)use($oldName){
                if(isset($params['info'][$oldName])){
                    $params['info'][$params['field']->name()]=$params['info'][$oldName];
                }
            })->setIndexRowDo(function(&...$params)use($oldName){
                if(isset($params['row'][$oldName])){
                    $params['row'][$params['field']->name()]=$params['info'][$oldName];
                }
            });

        return $field;

    }

}