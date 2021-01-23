<?php


namespace tpScriptVueCurd;


use tpScriptVueCurd\filter\EmptyFilter;

/**
 * Class ModelField
 * @package tpScriptVueCurd
 * @author tj 1079798840@qq.com
 */
abstract class ModelField
{
    protected string $name='';//字段名
    protected array $tag=[];//标签
    protected string $group='';//分组
    protected string $title='';//标题
    protected string $ext='';//后缀
    protected string $placeholder='';//输入提示
    protected bool $listShow=false;//是否在列表中显示
    protected int $listColumnWidth=0;//指定列宽（0，不指定）
    protected bool $required=false;//字段是否必填
    protected bool $readOnly=false;//找到是否只读
    protected bool $editShow=true;//字段在添加修改时是否显示
    protected string $type='';//字段类型，不可修改
    protected $save;//提交保存时，要提交的数据库的值
    protected ?ModelFilter $filter=null;//筛选对象
    protected string $defaultFilterClass='';//筛选类型
    protected bool $pub=false;//是否在前台公开
    protected bool $canExcelImport=true;//是否可以excel导入
    public const REQUIRED=true;//开启必填验证


    public function __construct(){
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
    public static function init(string $name=null,string $title=null,string $group=null): self
    {
        $obj=new static();
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


    /**
     * 是否在后台列表中显示出来
     * @param bool|null $listShow
     * @return $this|bool
     */
    public function listShow(bool $listShow=null){
        return $this->doAttr('listShow',$listShow);
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
     * 自定义其他属性
     * @param $name
     * @param null $value
     * @return $this
     * @throws \think\Exception
     */
    public function other($name, $value=null): self
    {
        if(method_exists($this,$name)){
            throw new \think\Exception('[ '.$name.' ]方法在'.(self::class).'中已存在，请直接调用，或更换属性名称');
        }
        return $this->doAttr($name,$value);
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
            }else{
                $data[$k]=$this->$k;
            }
        }
        return $data;
    }


    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSave(array $data): self
    {
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

}