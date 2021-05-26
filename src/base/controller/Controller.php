<?php


namespace tpScriptVueCurd\base\controller;


use think\App;
use think\db\Query;
use think\Request;
use tpScriptVueCurd\option\FunControllerIndexData;
use tpScriptVueCurd\option\FunControllerIndexPage;
use tpScriptVueCurd\traits\controller\Vue;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\traits\controller\CurdFunc;
use tpScriptVueCurd\traits\controller\Excel;
use tpScriptVueCurd\traits\Func;

/**
 * Trait Controller
 * @author tj 1079798840@qq.com
 * @property APP $app
 * @property Request $request
 * @property string $guid
 * @package tpScriptVueCurd\base\controller
 */
trait Controller
{
    use Func,Vue,CurdFunc,Excel{
        Vue::initialize as vueInitialize;
    }

    protected string $tplPath='';
    protected FunControllerIndexPage $indexPageOption;

    public function initialize()
    {
        if(empty($this->app)){
            $this->app=app();
        }
        if(empty($this->request)){
            $this->request=$this->app->request;
        }
        if(empty($this->guid)){
            if (!function_exists('create_guid')) {
                $charid = strtoupper(md5(uniqid(mt_rand(), true)));
                $hyphen = chr(45);// "-"
                $uuid = chr(123)// "{"
                    . substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12)
                    . chr(125);// "}"
                $this->guid=$uuid;
            }else{
                $this->guid=create_guid();
            }
        }
        $this->vueInitialize();


        $indexPageOption=new FunControllerIndexPage;
        static::setIndexPage($indexPageOption);
        $this->indexPageOption=$indexPageOption;


        $this->tplPath=root_path().'vendor'.DIRECTORY_SEPARATOR.'tj'.DIRECTORY_SEPARATOR.'tp-script-vue-curd'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR;
        $this->assign('jsPath','/tp-script-vue-curd-static.php?default.js');
    }

    /**
     * 控制器的标题
     * @return string
     */
    abstract public static function getTitle():string;

    /**
     * @return string|VueCurlModel
     */
    abstract public static function modelClassPath():string;


    /**
     * 控制器类型：base、child、base_have_child
     * @return string
     */
    abstract public static function type():string;


    /**
     * 列表分页配置
     * @return FunControllerIndexPage
     */
    abstract public static function setIndexPage(FunControllerIndexPage $indexPageOption):void;


    /**
     * 列表自定义条件
     * @param Query $query
     */
    protected function indexListWhere(Query $query):void{

    }

    protected function indexData(FunControllerIndexData $option):void{
        //列表数据处理钩子
    }
    protected function indexFetch(array &$fetch):void{
        // 列表页面显示前处理
    }
    
    protected function addAfter(VueCurlModel $info): void
    {
        // 数据添加钩子，方便之类处理（之类重写此方法）
    }
    protected function editAfter(VueCurlModel $info): void
    {
        // 数据修改钩子，方便之类处理（之类重写此方法）
    }

    protected function createEditFetchDataBefore(FieldCollection $fields, ?VueCurlModel &$data):void
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




}