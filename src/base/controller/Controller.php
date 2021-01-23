<?php


namespace tpScriptVueCurd\base\controller;


use think\App;
use think\db\Query;
use think\Request;
use tpScriptVueCurd\traits\controller\Vue;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\traits\controller\Curd;
use tpScriptVueCurd\traits\controller\CurdFunc;
use tpScriptVueCurd\traits\controller\Excel;

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
    use Vue,CurdFunc,Excel{
        Vue::initialize as vueInitialize;
    }

    protected string $tplPath='';

    public function initialize()
    {
        $this->vueInitialize();
        if(empty($this->app)){
            $this->app=app();
        }
        if(empty($this->request)){
            $this->app=$this->app->request;
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


        $this->tplPath=root_path().'vendor'.DIRECTORY_SEPARATOR.'tj'.DIRECTORY_SEPARATOR.'tp-script-vue-curd'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR;

        $this->assign('guid',$this->guid);
        $this->assign('vueCurdAction',$this->request->action());
        $this->assign('vueCurdController',$this->request->controller());
        $this->assign('vueCurdModule',$this->app->http->getName());
        //TODO::要动态的
        $this->assign('vueCurdVersion','1.0');

        $this->assign('loginUrl',getLoginUrl());
    }

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
     * 列表自定义条件
     * @param Query $query
     */
    protected function indexListWhere(Query $query):void{

    }

    protected function indexData(array $data):array{
        //列表数据处理钩子
        return $data;
    }
    protected function indexFetch(array $fetch):array{
        // 列表页面显示前处理
        return $fetch;
    }

    protected function addAfter(VueCurlModel $info): void
    {
        // 数据添加钩子，方便之类处理（之类重写此方法）
    }
    protected function editAfter(VueCurlModel $info): void
    {
        // 数据修改钩子，方便之类处理（之类重写此方法）
    }

    protected function createEditFetchDataBefore(FieldCollection $fields, ?VueCurlModel $data):void
    {
        //（添加/编辑页面）生成解析数据前，处理数据
    }
    protected function beforeAddShow(array $fetchData):array{
        //数据添加页面 解析前
        return $fetchData;
    }
    protected function beforeEditShow(array $fetchData):array{
        //数据修改页面 解析前
        return $fetchData;
    }




}