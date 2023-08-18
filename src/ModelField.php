<?php


namespace tpScriptVueCurd;


use think\Collection;
use think\Exception;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\field\ListField;
use tpScriptVueCurd\field\StringField;
use tpScriptVueCurd\field\TableField;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\option\field\edit_on_change\EditOnChange;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldEditTip;
use tpScriptVueCurd\option\FieldStep;
use tpScriptVueCurd\option\FieldStepCollection;
use tpScriptVueCurd\option\FieldTip;
use tpScriptVueCurd\option\FieldWhere;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\option\grid\GridCol;
use tpScriptVueCurd\tool\ErrorCode;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\Func;

/**
 * Class ModelField
 * @package tpScriptVueCurd
 * @author tj 1079798840@qq.com
 */
abstract class ModelField
{
    use Func;

    protected string $guid = '';
    protected string $name = '';//字段名
    protected array $tag = [];//标签
    protected string $group = '';//分组
    protected string $title = '';//标题
    protected string $ext = '';//后缀
    protected string $placeholder = '';//输入提示
    protected string $editExplain = '';//字段编辑时下方的说明
    protected string $editExplainColor='rgb(134,144,156)';//字段编辑时，底部显示的颜色
    protected string $explain='';//字段编辑和显示时，底部显示
    protected string $explainColor='rgb(134,144,156)';//字段编辑和显示时，底部显示
    protected bool $listShow = false;//是否在列表中显示
    protected int $listColumnWidth = 0;//指定列宽（0，不指定）
    protected bool $listSort = true;//列表中时候可排序
    protected string $listFixed = '';//列表中，列是否浮动，'left'/'right'
    protected bool $required = false;//字段是否必填
    protected bool $readOnly = false;//找到是否只读
    protected bool $editShow = true;//字段在添加修改时是否显示
    protected bool $showPage = true;//字段在详情页面中是否显示
    protected string $type = '';//字段类型，不可修改
    protected $save;//提交保存时，要提交的数据库的值
    protected ?ModelFilter $filter = null;//筛选对象
    protected string $defaultFilterClass = '';//筛选类型
    protected bool $pub = false;//是否在前台公开
    protected bool $canExcelImport = true;//是否可以excel导入
    protected ?array $editLabelCol = null;//编辑界面 label布局
    protected string $editLabelAlign = 'right';//编辑界面 label对齐
    protected ?array $editWrapperCol = null;//编辑界面 为输入控件设置布局样式 用法同 editLabelCol
    protected bool $editColon = true;//是否显示label后面的冒号
    protected bool $showUseComponent = false;//查看页面这一行，完全使用组件自己的显示（不显示左边的标题）
    protected $validateRule = null;//数据验证
    protected $nullVal;//字段在数据库中为空时的值
    protected bool $canSaveZero=false;//是否可保存为0，验证为空的时候跳过0
    /**
     * @var FieldDo[] $fieldDoList
     */
    protected array $fieldDoList = [];//数据列表时执行，数据显示时执行（方便一些数据处理，也可以叫字段钩子）
    protected ?FieldWhere $hideSelf = null;//编辑时是否隐藏本字段
    /**
     * @var FieldEditTip[] $editTips 字段在编辑页面时，可根据填写的内容显示不同的提示信息
     */
    protected array $editTips = [];
    protected array $tips = [];//字段编辑和显示时显示在字段顶部的提示
    public const REQUIRED = true;//开启必填验证
    public bool $objWellToArr = true;
    protected array $attrWhereValueList=[];
    /**
     *
     * @var $generateColumn null|bool|callable
     */
    protected $generateColumn=null;
    protected array $listColStyle=[];//列表中单元格内的样式

    /**
     * @var string 调用toArray的时候，toArray自行根据此值判断处理，防止多余的数据到前台，影响性能
     * listColumns  列表中表头
     * edit 边界
     * show 显示
     */
    public string $toArrayPageType='';
    protected $toArrayBefore=null;


    protected bool $canExport=true;//是否可以导出本字段数据

    protected FieldTpl $componentTpls;
    protected ?GridCol $grid=null;
    protected $gridByEdit=null;
    protected $gridByShow=null;


    /**
     * 监听值变化
     * @var null|EditOnChange
     */
    protected ?EditOnChange $editOnChange=null;

    public function __construct()
    {
        $this->guid = create_guid();
        $this->type = class_basename(static::class);
        if ($this->defaultFilterClass) {
            $this->filter(new $this->defaultFilterClass($this));
        }
        $this->componentTpls=static::componentUrl();
    }

    /**
     * 初始化子类
     * @param string|null $name
     * @param string|null $title
     * @param string|null $group
     * @return $this
     */
    public static function init(string $name = null, string $title = null, string $group = null, $otherConfig = null): self
    {
        $obj = new static($otherConfig);
        is_null($name) || $obj->name($name);
        is_null($title) || $obj->title($title);
        is_null($group) || $obj->group($group);
        return $obj;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**字段名
     * @param string|null $name
     * @return $this|string
     */
    public function name(string $name = null)
    {
        return $this->doAttr('name', $name);
    }

    /**字段标题
     * @param string|null $title
     * @return $this|string
     */
    public function title(string $title = null)
    {
        return $this->doAttr('title', $title);
    }

    /**分组
     * @param string|null $group
     * @return $this|string
     */
    public function group(string $group = null)
    {
        return $this->doAttr('group', $group);
    }

    /**标签（多个）
     * @param string|null $tag
     * @return $this|array
     */
    public function tag(string $tag = null)
    {
        if (is_null($tag)) {
            return $this->tag;
        }
        $this->tag[] = $tag;
        return $this;
    }

    /**
     * 要删除的标签
     * @param array $tags
     * @return $this
     */
    public function removeTag(array $tags): self
    {
        $this->tag = array_diff($this->tag, $tags);
        return $this;
    }


    /**
     * 清空标签
     * @return $this
     */
    public function clearTag(): self
    {
        $this->tag = [];
        return $this;
    }

    /**结尾
     * @param string|null $ext
     * @return $this|string
     */
    public function ext(string $ext = null)
    {
        return $this->doAttr('ext', $ext);
    }

    /**输入提示
     * @param string|null $placeholder
     * @return $this|string
     */
    public function placeholder(string $placeholder = null)
    {
        return $this->doAttr('placeholder', $placeholder);
    }

    /**字段编辑时下方的说明
     * @param string|null $editExplain
     * @return $this|string
     */
    public function editExplain(string $editExplain = null)
    {
        return $this->doAttr('editExplain', $editExplain);
    }

    /**字段编辑时下方的说明字体颜色
     * @param string|null $editExplainColor
     * @return $this|string
     */
    public function editExplainColor(string $editExplainColor = null)
    {
        return $this->doAttr('editExplainColor', $editExplainColor);
    }


    /**字段编辑和显示时下方的说明
     * @param string|null $explain
     * @return $this|string
     */
    public function explain(string $explain = null)
    {
        return $this->doAttr('explain', $explain);
    }
    /**字段编辑和显示时下方的说明字体颜色
     * @param string|null $explainColor
     * @return $this|string
     */
    public function explainColor(string $explainColor = null)
    {
        return $this->doAttr('explainColor', $explainColor);
    }

    /**
     * 是否在后台列表中显示出来
     * @param bool|null $listShow
     * @return $this|bool
     */
    public function listShow(bool $listShow = null)
    {
        return $this->doAttr('listShow', $listShow);
    }

    /**列表中，列是否浮动，'left'/'right'
     * @param string|null $listFixed
     * @return $this|string
     */
    public function listFixed(string $listFixed = null)
    {
        return $this->doAttr('listFixed', $listFixed);
    }


    /**
     * 列宽度
     * @param int|null $listColumnWidth
     * @return $this|bool
     */
    public function listColumnWidth(int $listColumnWidth = null)
    {
        return $this->doAttr('listColumnWidth', $listColumnWidth);
    }

    /**
     * 列表中时候可排序
     * @param bool|null $listSort
     * @return $this|bool
     */
    public function listSort(bool $listSort = null)
    {
        return $this->doAttr('listSort', $listSort);
    }

    /**
     * 是否必填
     * @param bool|null $required
     * @return $this|bool
     */
    public function required(bool $required = null)
    {
        if (is_null($required)) {
            return self::REQUIRED && $this->required;
        }
        $this->required = $required;
        $this->fieldPushAttrByWhere('required',$required);
        return $this;
    }

    /**
     * 字段是否只读
     * @param bool|null $readOnly
     * @return $this|bool
     */
    public function readOnly(bool $readOnly = null)
    {
        if (is_null($readOnly)) {
            return $this->readOnly;
        }
        $this->readOnly = $readOnly;
        $this->fieldPushAttrByWhere('readOnly',$readOnly);
        return $this;
    }

    /**
     * 是否在前端公开
     * @param bool|null $pub
     * @return $this|bool
     */
    public function pub(bool $pub = null)
    {
        return $this->doAttr('pub', $pub);
    }

    /**
     * 是否可以excel模板导入
     * @param bool|null $canExcelImport
     * @return $this|bool
     */
    public function canExcelImport(bool $canExcelImport = null)
    {
        return $this->doAttr('canExcelImport', $canExcelImport);
    }

    /**
     * Excel 模版中的下拉选项
     * @return array
     */
    public function excelSelectItems(){
        return [];
    }

    /**
     * 字段在添加修改时是否显示
     * @param bool|null $editShow
     * @return $this|bool
     */
    public function editShow(bool $editShow = null)
    {
        return $this->doAttr('editShow', $editShow);
    }


    /**
     * 字段在详情页面中是否显示
     * @param bool|null $showPage
     * @return $this|bool
     */
    public function showPage(bool $showPage = null)
    {
        return $this->doAttr('showPage', $showPage);
    }

    /**
     * 编辑界面 label布局
     * @param array|null $editLabelCol
     * @param bool $forceSet 是否强制设置值，当要设置值为null时使用
     * @return $this|array|null
     */
    public function editLabelCol(array $editLabelCol = null, bool $forceSet = false)
    {
        if ($forceSet) {
            $this->editLabelCol = $editLabelCol;
            $this->fieldPushAttrByWhere('editLabelCol',$editLabelCol);
            return $this;
        }
        return $this->doAttr('editLabelCol', $editLabelCol);
    }

    /**
     * 标签文本对齐方式
     * @param string|null $editLabelAlign
     * @return $this|string
     */
    public function editLabelAlign(string $editLabelAlign = null)
    {
        return $this->doAttr('editLabelAlign', $editLabelAlign);
    }

    /**
     * 编辑界面 为输入控件设置布局样式 用法同 editLabelCol
     * @param array|null $editWrapperCol
     * @param bool $forceSet 是否强制设置值，当要设置值为null时使用
     * @return $this|array|null
     */
    public function editWrapperCol(array $editWrapperCol = null, bool $forceSet = false)
    {
        if ($forceSet) {
            $this->editWrapperCol = $editWrapperCol;
            return $this;
        }
        return $this->doAttr('editWrapperCol', $editWrapperCol);
    }


    /**
     * 查看页面这一行，完全使用组件自己的显示（不显示左边的标题）
     * @param bool|null $showUseComponent
     * @return $this|bool
     */
    public function showUseComponent(bool $showUseComponent = null)
    {
        return $this->doAttr('showUseComponent', $showUseComponent);
    }

    /**
     * 是否显示label后面的冒号
     * @param bool|null $editColon
     * @return $this|bool
     */
    public function editColon(bool $editColon = null)
    {
        return $this->doAttr('editColon', $editColon);
    }


    /**
     * 数据提交验证，可填入tp的内置规则名称（https://www.kancloud.cn/manual/thinkphp6_0/1037629），或者写一个函数
     * @param $validateRule
     * @return $this|mixed
     */
    public function validateRule($validateRule = null)
    {
        return $this->doAttr('validateRule', $validateRule);
    }


    /**
     * 列表中单元格内的样式
     * @param array|null $listColStyle
     * @return $this|array
     */
    public function listColStyle(array $listColStyle = null)
    {
        return $this->doAttr('listColStyle', $listColStyle);
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
    public function hideSelf(): ?FieldWhere
    {
        return $this->hideSelf ?? null;
    }

    /**
     * 新增隐藏本字段条件
     * @param FieldWhere $where
     * @return $this
     */
    public function pushHideSelfWhere(FieldWhere $where): self
    {
        if (!$this->hideSelf()) {
            $this->hideSelf = FieldWhere::make(StringField::init(FieldWhere::RETURN_FALSE_FIELD_NAME), FieldWhere::RETURN_FALSE_FIELD_NAME . '[这个条件是初始化条件，不用管]');
        }
        $this->hideSelf->or($where);
        return $this;
    }


    /**
     * 字段在编辑页面时，可根据填写的内容显示不同的提示信息
     * @return FieldEditTip[]
     */
    public function editTips(): array
    {
        return $this->editTips;
    }

    /**
     * 字段在编辑页面时，可根据填写的内容显示不同的提示信息
     * @return FieldTip[]
     */
    public function tips(): array
    {
        return $this->tips;
    }

    /**
     * 字段属性的值的集合
     * @return array
     */
    public function attrWhereValueList():array{
        return $this->attrWhereValueList;
    }

    /**
     * 根据条件设置字段的属性（只能设置下面几种值的属性，），将会在前台显示时、数据保存时，编辑显示时，满足条件的时候设置为 这里的$val
     * @param string $attr 属性
     * @param string|int|bool|float|null|array $val 值只能是这三种类型
     * @param FieldWhere|null $where 条件，如果为null，表示直接达成条件
     * @return $this
     * @throws Exception
     */
    public function pushAttrByWhere(string $attr,$val,?FieldWhere $where):self{
//        if(!is_string($val) && !is_int($val) && !is_float($val) && !is_bool($val) && !is_null($val) && !($this instanceof ListField && $attr === 'fields' && $val instanceof FieldCollection)) {
//            throw new \think\Exception('不能使用pushAttrByWhere设置'.$attr.'为'.gettype($val).'类型的值');
//        }


        if($attr==='name'||$attr==='type'||$attr==='guid'||$attr==='group'){
            throw new \think\Exception($attr.'不能通过pushAttrByWhere改变');
        }
        isset($this->attrWhereValueList[$attr])||$this->attrWhereValueList[$attr]=[];
        $this->attrWhereValueList[$attr][] = [
            'value' => $val,
            'where' => $where,
        ];
        return $this;
    }

    protected function fieldPushAttrByWhere(string $attr,$val):self{
        try{
            $this->pushAttrByWhere($attr,$val,null);
        }catch (\Exception $exception){}
        return $this;
    }


    /**
     * 验证数据是否符合条件
     * @param array $saveDatas
     * @param bool $isSourceData 是否数据为源数据，未经过字段的setSave处理
     * @param BaseModel|null $info  原数据
     * @return $this
     */
    public function setAttrValByWheres(array $saveDatas,bool $isSourceData,BaseModel $info):self{
        $def='--setAttrValByWheres--def--';
        foreach ($this->attrWhereValueList as $attr=>$valList){
            $value=$this->$attr??$def;
            foreach (array_reverse($valList) as $valArr){
                /**
                 * @var FieldWhere|null $where
                 */
                $val=$valArr['value'];
                $where=$valArr['where'];
                if(is_null($where)||$where->check($saveDatas,$isSourceData,$info)){
                    $value=$val;
                    break;
                }
            }

            if(isset($this->$attr)){
                if($value!==$this->$attr){
                    $this->$attr=$value;
                }
            }else{
                if($value!==$def){
                    $this->$attr=$value;
                }
            }

            if($value!==$this->$attr&&$value!==$def){
                $this->$attr=$value;
            }
        }
        return $this;
    }


    /**
     * 字段在编辑时显示的备注信息，不能与pushEditTip方法混用
     * @param string|null $message
     * @return $this|FieldEditTip|null
     */
    public function editTip(string $message=null){
        if(is_null($message)){
            return $this->editTips[0]??null;
        }
        $this->editTips=[new FieldEditTip($message, null)];
        return $this;
    }

    /**
     * 字段在编辑页面时，可根据填写的内容显示不同的提示信息|设置，不能与editTip方法混用
     * @param string $message
     * @param FieldWhere|null $show
     * @return FieldEditTip
     */
    public function pushEditTip(string $message, FieldWhere $show=null): FieldEditTip
    {
        $fieldEditTip = new FieldEditTip($message, $show);
        $this->editTips[] = $fieldEditTip;
        return $fieldEditTip;
    }

    /**
     * 字段在编辑和显示页面时，可根据填写的内容显示不同的提示信息|设置
     * @param string $message
     * @param FieldWhere|null $show
     * @return FieldTip
     */
    public function pushTip(string $message, FieldWhere $show=null): FieldTip
    {
        $fieldTip = new FieldTip($message, $show);
        $this->tips[] = $fieldTip;
        return $fieldTip;
    }


    /**
     * 自定义其他属性
     * @param $name
     * @param null $value
     * @return $this|null|mixed
     * @throws \think\Exception
     */
    public function other($name, $value = null)
    {
        if (method_exists($this, $name)) {
            throw new \think\Exception('[ ' . $name . ' ]方法在' . (self::class) . '中已存在，请直接调用，或更换属性名称');
        }
        if (is_null($value)) {
            return $this->$name ?? null;
        }
        $this->$name = $value;
        $this->fieldPushAttrByWhere($name,$value);
        return $this;
    }


    protected function doAttr($name, $value)
    {
        if (is_null($value)) {
            return $this->$name;
        }
        $this->$name = $value;
        $this->fieldPushAttrByWhere($name,$value);
        return $this;
    }


    /**
     * 有时候数据太多，传到了前台，影响性能，可用此函数处理，根据toArrayPageType判断以什么方式处理
     * @param callable $func
     * @return $this
     */
    public function setToArrayBefore(callable $func):self{
        $this->toArrayBefore=$func;
        return $this;
    }

    public function toArray(): array
    {
        $field=clone $this;

        $data = [];

        $func=$field->toArrayBefore;
        if($func){
//            $field->toArrayPageType;
            //里面可动态改变$data
            $funcReturn=$func($field,$data);
            if(!is_null($funcReturn)&&is_array($funcReturn)){
                //如果有返回值，且返回数组，就直接返回当前
                return $funcReturn;
            }
        }

        $toArr=function (&$arr)use(&$toArr){
            foreach ($arr as $arrK=>$arrV){
                if(is_array($arrV)){
                    $toArr($arr[$arrK]);
                }else if (is_object($arrV) && method_exists($arrV, 'toArray')){
                    $arr[$arrK]=$arrV->toArray();
                }
            }
        };


        foreach (get_class_vars(static::class) as $k => $v) {
            if (method_exists($field, $k)) {
                $data[$k] = $field->$k();
            } elseif (!isset($field->$k)) {
                continue;
            } else {
                $data[$k] = $field->$k;
            }

            if (is_array($data[$k])) {
                if($field->objWellToArr){
                    $toArr($data[$k]);
                }
            } else if (is_object($data[$k])) {
                if (method_exists($data[$k], 'toArray')) {
                    if ($field->objWellToArr) {
                        $data[$k] = $data[$k]->toArray();
                    } else {
                        unset($data[$k]);
                    }
                }
            }
        }
        return $data;
    }


    /**
     * 设置保存的值，子类不能重写
     * @param array $data 数据值集合
     * @return $this
     */
    final public function setSave(array $data,BaseModel $old): self
    {
        $this->setSaveVal($data,$old);
        if (isset($this->save)) {
            $checkData = $data;
            $checkData[$this->name()] = $this->save;
            $this->validate($checkData, false);
        }
        return $this;
    }


    /**
     * 设置为空值（子字段有需要的话，继承重写）
     * @return $this
     * @throws \think\Exception
     */
    public function setSaveToNull(): self
    {
        $nullVal = $this->nullVal();
        $this->defaultCheckRequired($nullVal);
        $this->save = $nullVal;
        return $this;
    }

    /**
     * 字段为空时的值
     * @return mixed
     */
    public function nullVal()
    {
        if(!isset($this->nullVal)){
            $generateColumn=$this->generateColumn;
            try{
                $field=new GenerateColumnOption($this->name());
            }catch (\Exception $exception){}
            if(isset($field)){
                $this->getGenerateColumnConfig($field);
                is_callable($generateColumn)&&$generateColumn($field);
                $this->nullVal=$field->getDefaultStr()??'';
            }else{
                $this->nullVal='';
            }
        }
        return $this->nullVal;
    }

    /**
     * 是否可以保存为0，验证为空的时候跳过0
     * @param bool|null $canSaveZero
     * @return $this|bool
     */
    public function canSaveZero(bool $canSaveZero=null){
        return $this->doAttr('canSaveZero', $canSaveZero);
    }

    /**
     * 设置字段在数据库中为空时的值
     * @param $nullVal
     * @return $this
     */
    public function setNullVal($nullVal): self
    {
        $this->nullVal = $nullVal;
        return $this;
    }


    /**
     * 详情与编辑页面布局
     * @param callable $editOrShowGrid
     * @param callable|null $showGrid  当为空时，使用编辑的页面布局
     * @return $this
     */
    public function gridBy(callable $editOrShowGrid,?callable $showGrid=null){
        /*$editOrShowGrid=function (BaseModel $info,ModelField $field){
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
    /**
     * 布局
     * @param GridCol|null $grid
     * @return $this|GridCol
     */
    public function grid(GridCol $grid=null){
        return $this->doAttr('grid', $grid);
    }
    /**
     * 取消
     * @return void
     */
    public function gridNull(){
        $this->grid=null;
    }
    public function getEditGridBy():?callable{
        return $this->gridByEdit;
    }
    public function getShowGridBy():?callable{
        return $this->gridByShow;
    }



    /**
     * 设置保存的值，子类可重写
     * @param array $data
     * @return $this
     * @throws \think\Exception
     */
    protected function setSaveVal(array $data,BaseModel $old): self
    {
        if (isset($data[$this->name()])) {
            $this->defaultCheckRequired($data[$this->name()]);
            $this->save = $data[$this->name()];
        }
        return $this;
    }


    public function getSave()
    {
        return $this->save;
    }


    /**
     * 执行数据验证，如果有验证规则的话
     * @param array $data
     * @param bool $throwTitle
     * @throws \think\Exception
     */
    final public function validate(array $data, bool $throwTitle): void
    {
        if (!$this->validateRule()) {
            return;
        }

        $title = '';
        if ($throwTitle) {
            if ($this->title()) {
                $title = '|' . $this->title();
            }
        } else {
            $title = '|ERROR_TITLE';
        }

        $validate = \think\facade\Validate::rule($this->name() . $title, $this->validateRule());
        if (!$validate->check($data)) {
            throw new \think\Exception(str_replace('ERROR_TITLE', '', $validate->getError()));
        }
    }


    /**
     *
     * 显示时要处理的数据
     * @param array $dataBaseData 从数据库中获取的数据
     */
    public function doShowData(array &$dataBaseData): void
    {
    }


    /**
     * 设置筛选，如果要不启用筛选，传入参数 new emptyFilter
     * @param ModelFilter|string|null $filter
     * @return $this|ModelFilter
     */
    public function filter($filter = null)
    {
        if ($filter){
            if ((is_string($filter) && $filter === EmptyFilter::class) || $filter instanceof EmptyFilter) {
                $this->filter = null;
            } else if (is_string($filter)) {
                $this->filter = new $filter($this);
            } else if(is_subclass_of($filter,ModelFilter::class)){
                $this->filter = $filter;
            }else{
                throw new \think\Exception($this->name().'的'.'filter参数错误');
            }
            return $this;
        }

        if (is_null($filter)) {
            return $this->filter;
        }
        throw new \think\Exception($this->name().'的'.'filter参数错误');
    }


    /**
     * 处理筛选(可调用filter对象的一些方法) ->doFilter(function($filter){ $filter->setItems(); })
     * @param $func
     * @return $this
     */
    public function doFilter($func): self
    {
        $filter = $this->filter();
        if (is_null($filter)) {
            return $this;
        }
        $func($filter,$this);
        return $this;
    }


    /**
     * 设置当前字段在列表中默认不显示筛选
     * @return $this
     */
    public function filterShow(): self
    {
        $this->doFilter(fn(ModelFilter $filter) => $filter->setShow(true));
        return $this;
    }

    /**
     * 设置当前字段在列表中默认不显示筛选
     * @return $this
     */
    public function filterHide(): self
    {
        $this->doFilter(fn(ModelFilter $filter) => $filter->setShow(false));
        return $this;
    }

    /**
     * 设置字段没有筛选
     * @return $this
     */
    public function filterEmpty(): self{
        $this->filter(new EmptyFilter());
        return $this;
    }


    /**
     * 模板导入时备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    abstract public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void;


    /**
     * EXCEL导入时，对数据的处理（之后再执行setSave）
     * @param array $save
     */
    public function excelSaveDoData(array &$save): void
    {
        //默认不用处理
    }


    /**
     * 默认是否为空判断，在 setSave 里面调用
     * @param $val
     * @param string $msg
     * @throws \think\Exception
     */
    final protected function defaultCheckRequired($val, string $msg = '不可为空'): void
    {
        if(!$this->required()){
            return;
        }
        if(($val===0||$val==='0')&&$this->canSaveZero()){
            return;
        }
        if((empty($val))||$this->nullVal() === $val){
            throw new \think\Exception($msg,ErrorCode::SAVE_IS_REQUIRED);
        }
    }


    /**
     * 字段模板配置
     * @return FieldTpl
     */
    abstract public static function componentUrl(): FieldTpl;


    protected FieldStepCollection $steps;

    /**
     * 字段所属步奏配置
     * @param null $steps
     * @return $this|FieldStepCollection|null
     * @throws \think\Exception
     */
    public function steps($steps = null)
    {
        if (is_null($steps)) {
            return $this->steps ?? null;
        }

        if (is_array($steps)) {
            $stepList = FieldStepCollection::make();
            foreach ($steps as $v) {
                $stepList->push(clone $v);
                $v->removeFieldData();
            }
        } else if ($steps instanceof FieldStep) {
            $stepList = FieldStepCollection::make([clone $steps]);
            $steps->removeFieldData();
        } else {
            throw new \think\Exception($this->name() . ' 的steps设置类型错误');
        }


        $stepList = $stepList->map(function (FieldStep $val) {
            $val->setFieldName($this->name());
            return $val;
        });
        $this->steps = $stepList;
        return $this;
    }


    /**
     * 设置 steps 和group,如果没有传入group，group为步骤标题
     * @param $step
     * @param string|null $group
     * @return $this
     * @throws \think\Exception
     */
    public function setStepsAndGroup($step, string $group = null): self
    {
        $this->steps($step);
        if (is_null($group)) {
            $group = $this->steps()[0]->getTitle();
        }
        $this->group($group);
        return $this;
    }


    /**
     * 如果传入有值，返回$this；如果传入无值，会在fieldDoList中新建一个并返回新建的FieldDo
     * @param FieldDo|null $fieldDo
     * @return $this|FieldDo
     */
    public function pushFieldDo(FieldDo $fieldDo = null)
    {
        if (!is_null($fieldDo)) {
            $this->fieldDoList[] = $fieldDo;
            return $this;
        }
        $fieldDo = new FieldDo;
        $this->fieldDoList[] = $fieldDo;
        return $fieldDo;
    }


    /**
     * 获取FieldDo列表
     * @return FieldDo[]
     */
    public function getFieldDoList(): array
    {
        return $this->fieldDoList;
    }

    public function guid(): string
    {
        return $this->guid;
    }


    /**
     * 复制字段，且重新设置字段名
     * @param null|bool|string $newName 新的字段名，如果为null自动生成生成。如果是string，表示为新的字段名
     * @param bool $saveSetOldName 是否 保存数据库时，使用的字段名称为老的字段的名称
     * @return $this
     */
    public function cloneField(string $newName = null, bool $saveSetOldName = true): self
    {
        $field = clone $this;

        $oldName = $field->name();
        if (is_null($newName)) {
            $newName = '__CLONE__' . $oldName;
        }

        $field->name($newName);

        //克隆的字段在数据库中有克隆的字段的字段名
        if (!$saveSetOldName) {
            return $field;
        }

        $field->pushFieldDo()
            ->setEditShowDo(function (BaseModel &$info, ?BaseModel $base, ModelField $field, $isStepNext) use ($oldName) {
                if (isset($info[$oldName])) {
                    $info[$field->name()] = $info[$oldName];
                }
            })
            ->setSaveBeforeCheckedDo(function (array &$postData, BaseModel $before, ?BaseModel $base, ModelField $field) use ($oldName) {
                if (isset($postData[$field->name()])) {
                    $postData[$oldName] = $postData[$field->name()];
                }
            })->setShowInfoBeforeDo(function (BaseModel $info, ?BaseModel $base, ModelField $field) use ($oldName) {
                if (isset($info[$oldName])) {
                    $info[$field->name()] = $info[$oldName];
                }
            })->setIndexRowDo(function (BaseModel $row, ?BaseModel $base, ModelField $field) use ($oldName) {
                if (isset($row[$oldName])) {
                    $row[$field->name()] = $row[$oldName];
                }
            });

        return $field;

    }


    /**
     * 子字段可重写
     * @param BaseModel $data
     * @param BaseModel|null $parentInfo
     * @return void
     */
    public function onEditShow(BaseModel &$data,BaseModel &$parentInfo=null):void{}
    /**
     * 子字段可重写
     * @param array $postData
     * @param BaseModel $old
     * @param BaseModel|null $parentInfo
     * @return void
     */
    public function onEditSave(array &$postData,BaseModel &$old=null,BaseModel &$parentInfo=null):void{}
    /**
     * 子字段可重写
     * @param BaseModel $data
     * @param BaseModel|null $parentInfo
     * @return void
     */
    public function onShow(BaseModel &$data,BaseModel &$parentInfo=null):void{}

    /**
     * 子字段可重写
     * @param BaseModel|null $parentInfo
     * @return void
     */
    public function onIndexShow(BaseModel &$parentInfo=null):void{}
    /**
     * 子字段可重写
     * @param Collection $list
     * @param BaseModel|null $parentInfo
     * @return void
     */
    public function onIndexList(Collection $list,BaseModel &$parentInfo=null):void{}


    /**
     * 是否要生成字段 function(GenerateColumnOption $option){$option->setTypeVarchar(80);}
     * @param bool|null|callable $generateColumn
     * @return $this|bool|callable
     */
    public function generateColumn($generateColumn = null)
    {
        if($generateColumn===null){
            return $this->generateColumn ?? true;
        }
        $this->generateColumn=$generateColumn;
        return $this;
    }
    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    abstract public function getGenerateColumnConfig(GenerateColumnOption $option):void;


    /**
     * 是否可以导出本字段数据
     * @param bool|null $canExport
     * @return $this|bool
     */
    public function canExport(bool $canExport = null)
    {
        return $this->doAttr('canExport', $canExport);
    }

    /**
     * 获取导出数据时文本
     * @param array $data
     * @return string
     */
    public function getExportText(array $data):string{
        return $data[$this->name()]??'';
    }

    /**
     * 当页面中此字段的值改变时，会执行此方法，此方法与表单提交的地址有关
     * 如果参数是回调函数，仅支持form下直属字段，否则(ListField、TableField情况下)请传入url
     * @param callable|string|null|EditOnChange $editOnChange
     * @param null|string|int|bool|float|array $val
     * @param null|FieldWhere $onWhere keyVal模式下有效
     * @return $this|EditOnChange
     * @throws Exception
     */
    public function editOnChange($editOnChange=null,$val=null,FieldWhere $onWhere=null){
        if($editOnChange===null){
            return $this->doAttr('editOnChange', $editOnChange);
        }
        if($editOnChange instanceof EditOnChange){
            return $this->doAttr('editOnChange', $editOnChange);
        }

        if(is_callable($editOnChange)){
            $editOnChangeObj=new \tpScriptVueCurd\option\field\edit_on_change\type\Func();
            $editOnChangeObj->func=$editOnChange;

        }else if(is_null($val)&&is_string($editOnChange)){
            $editOnChangeObj=new \tpScriptVueCurd\option\field\edit_on_change\type\Url();
            $editOnChangeObj->url=$editOnChange;
        }else if(is_string($editOnChange)&&!is_null($val)){
            if(strpos($editOnChange,'form.')!==0&&strpos($editOnChange,'fields.')!==0){
                throw new \think\Exception($this->name().' 字段 editOnChange 的参数错误-001');
            }
            if(is_array($val)||is_string($val)||is_int($val)||is_float($val)||is_bool($val)){
                $editOnChangeObj=new \tpScriptVueCurd\option\field\edit_on_change\type\KeyVal();
                $editOnChangeObj->key=$editOnChange;
                $editOnChangeObj->val=$val;
                $editOnChangeObj->where=$onWhere;
            }else{
                throw new \think\Exception($this->name().' 字段 editOnChange 的参数错误-002');
            }
        }else{
            throw new \think\Exception($this->name().' 字段 editOnChange 的参数错误-003');
        }
        //方法与url需返回要修改的form与fields
        return $this->doAttr('editOnChange', $editOnChangeObj);
    }


    /**
     * 设置字段的模板
     * @param string|null $key
     * @param string|Index|Show|Edit $url
     * @return $this|FieldTpl
     */
    public function componentTpls(string $key=null,$url=null){
        if(is_null($key)){
            return $this->componentTpls;
        }
        switch ($key){
            case 'index':
                if(is_string($url)){
                    $this->componentTpls->index->jsUrl=$url;
                }else if($url instanceof Index){
                    $this->componentTpls->index=$url;
                }

                break;
            case 'show':
                if(is_string($url)){
                    $this->componentTpls->show->jsUrl=$url;
                }else if($url instanceof Show){
                    $this->componentTpls->show=$url;
                }
                break;
            case 'edit':
                if(is_string($url)){
                    $this->componentTpls->edit->jsUrl=$url;
                }else if($url instanceof Edit){
                    $this->componentTpls->edit=$url;
                }
                break;
        }
        return $this;
    }


    /**
     * 子类重写用的。子类需要额外载入字段js文件时使用
     * @return FieldCollection|null
     */
    public function getOtherComponentJsFields():?FieldCollection{
        return null;
    }

}