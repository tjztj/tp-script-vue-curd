<?php


namespace tpScriptVueCurd\base\controller;


use think\App;
use think\db\Query;
use think\helper\Str;
use think\Request;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\field\FilesField;
use tpScriptVueCurd\option\FunControllerImportAfter;
use tpScriptVueCurd\option\FunControllerImportBefore;
use tpScriptVueCurd\option\FunControllerIndexData;
use tpScriptVueCurd\option\FunControllerIndexPage;
use tpScriptVueCurd\traits\controller\BaseDel;
use tpScriptVueCurd\traits\controller\BaseEdit;
use tpScriptVueCurd\traits\controller\BaseIndex;
use tpScriptVueCurd\traits\controller\BaseShow;
use tpScriptVueCurd\traits\controller\Bpmn;
use tpScriptVueCurd\traits\controller\Childs;
use tpScriptVueCurd\traits\controller\Export;
use tpScriptVueCurd\traits\controller\HaveChilds;
use tpScriptVueCurd\traits\controller\TreeIndex;
use tpScriptVueCurd\traits\controller\Vue;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\traits\controller\Excel;

/**
 * Trait Controller
 * @author tj 1079798840@qq.com
 * @property APP $app
 * @property Request $request
 * @property string $guid
 * @property FieldCollection $fields;
 */
trait Controller
{
    use Vue,BaseIndex,TreeIndex,BaseShow,BaseEdit,BaseDel,HaveChilds,Childs,Excel,Export,Bpmn{
        Vue::initialize as vueInitialize;
    }

    /**
     * @var BaseModel
     */
    public $md;
    public FieldCollection $fields;
    public string $title='';


    public bool $dontShowTpl=false;
    public bool $dontShowTplAndGetData=false;
    public string $fetchPath;
    public string $tplPath='';
    public FunControllerIndexPage $indexPageOption;


    public function initialize()
    {
        if(empty($this->app)){
            $this->app=app();
        }
        if(empty($this->request)){
            $this->request=$this->app->request;
        }
        if(empty($this->guid)){
            $this->guid=create_guid();
        }
        $this->vueInitialize();


        $indexPageOption=new FunControllerIndexPage;
        $this->indexPageOption=$indexPageOption;
        $this->tplPath=getVCurdDir().'tpl'.DIRECTORY_SEPARATOR;
        $this->assign('jsPath','/tpscriptvuecurd/default.js');


        $this->init();

        $this->fields=$this->md->fields();
    }


    /**
     * 控制器初始化配置
     */
    abstract public function init():void;



    /**
     * 生成控制器对象
     * @param BaseModel|null $model
     * @return static
     */
    public static function make(BaseModel $model=null):self{
        $return=new static(app());
        is_null($model)||$return->md=$model;
        return $return;
    }


    /**
     * 列表自定义条件
     * @param Query $query
     */
    protected function indexListWhere(Query $query):void{

    }
    protected function indexShowBefore(?BaseModel &$parentInfo):void{
        //要改fields，可以直接在 这里 $this->fields
        // 列表页面显示前处理，在indexFetch前
    }
    protected function indexFetch(array &$fetch):void{
        // 列表页面显示前处理，在indexShowBefore后
    }
    protected function indexData(FunControllerIndexData $option):void{
        //列表数据处理钩子
    }

    protected function setPostDataBefore(array &$post):void{
        //用户提交的数据处理
    }

    protected function addAfter(BaseModel $info): void
    {
        // 数据添加成功后钩子，方便子类处理（子类重写此方法）
    }
    protected function editAfter(BaseModel $info): void
    {
        // 数据修改成功后钩子，方便子类处理（子类重写此方法）
    }

    protected function createEditFetchDataBefore(FieldCollection $fields, BaseModel &$data,?BaseModel $baseModel):void
    {
        //（添加/编辑页面）生成解析数据前，处理数据（控制字段显示与否），$data为空代表是新增
    }

    protected function showFetch(array $fetch):array{
        // 详情页面显示前处理
        return $fetch;
    }

    protected function beforeAddShow(array $fetchData):array{
        //数据添加页面 解析前
        return $fetchData;
    }
    protected function beforeEditShow(array $fetchData):array{
        //数据修改页面 解析前
        return $fetchData;
    }

    protected function beforeDel(array $ids):array{
        //删除前
        return $ids;
    }

    protected function afterDel(\think\Collection $delInfos):void{
        //删除后
    }

    public function importBefore(FunControllerImportBefore $option):void{
        // 数据导入前，方便之类处理（之类重写此方法）
    }

    public function importAfter(FunControllerImportAfter $option):void{
        // 数据导入后，方便之类处理（之类重写此方法）
    }

    protected function editBefore(FieldCollection &$fields,BaseModel $old,?BaseModel $parentInfo,?array &$data=null): void
    {
        // 数据添加或修改时，显示与提交都会执行此方法
    }

    protected function showBefore(BaseModel $info,?BaseModel $parentInfo,FieldCollection &$field){
        //数据显示前
    }


    /**
     * 显示模板内容
     * @param string $file      直接在控制器下面的模板位置添加模板文件就可替换默认的模板，或者使用fetchPath
     * @param $data
     * @return mixed
     */
    protected function showTpl($file,$data){
        FilesField::setShowFileInfos();
        if($this->dontShowTplAndGetData){
            return $data;
        }
        if($this->dontShowTpl){
            $this->success($data);
        }

        if(isset($this->fetchPath)&&$this->fetchPath!==''){
            return $this->fetch($this->fetchPath,$data);
        }

        $appName = $this->app->http->getName();
        $view    = $this->app->view->getConfig('view_dir_name');
        $depr =$this->app->view->getConfig('view_depr');

        $path = $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;
        if (!is_dir($this->app->getAppPath() . $view)&&$appName) {
            $path .= $appName . DIRECTORY_SEPARATOR;
        }
        $controller = $this->app->request->controller();
        if (strpos($controller, '.')) {
            $pos        = strrpos($controller, '.');
            $controller = substr($controller, 0, $pos) . '.' . Str::snake(substr($controller, $pos + 1));
            $controller_name=Str::snake(substr($controller, $pos + 1));
        } else {
            $controller = Str::snake($controller);
            $controller_name=$controller;
        }
        $template=$file?:Str::snake( $this->app->request->action());
        $path .= str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . ($file ?: Str::snake($this->app->request->action())) . '.vue';
        if(file_exists($path)){
            return $this->fetch(str_replace('.', '/', $controller).'/'.$template,$data);
        }
        $tplPath=static::getTplPath();
        return $this->fetch($tplPath.$file.'.vue',$data);
    }

}