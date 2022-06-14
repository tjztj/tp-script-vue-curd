<?php
namespace tpScriptVueCurd\option\single;

use think\App;
use think\Model;
use tpScriptVueCurd\field\FilesField;
use tpScriptVueCurd\field\RegionField;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\traits\Func;

class Edit
{
    use Func;

    public string $title='';//标题
    public FieldCollection $fields;
    /**
     * @var null|Model|array
     */
    public $info=null;
    public string $subBtnTitle='确定提交';//提交按钮名称
    public string $refreshBtnTitle='刷新页面';//刷新按钮名称，如果设置为空，不显示刷新按钮
    public string $saveUrl='';//提交地址
    public string $bgColor='#f0f2f5';//页面背景色


    private string $guid;
    private APP $app;
    public string $jsPath='/tp-script-vue-curd-static.php?single/edit.js';

    public function __construct()
    {
        $this->app=app();
        $this->app->view->config(['view_suffix' => 'vue']);
        $this->app->view->engine()->layout(getVCurdDir().'tpl'.DIRECTORY_SEPARATOR.'layout'.DIRECTORY_SEPARATOR.'default.vue');
        $this->guid=create_guid();
    }

    public function fetch(){
        if(!isset($this->fields)||is_null($this->fields)){
            throw new \think\Exception('需设置fields');
        }
        if(empty($this->saveUrl)){
            throw new \think\Exception('需设置saveUrl');
        }
        $this->fields->each(function (ModelField $v){
            if($v->title()===''){
                $v->title($v->name());
            }
            if(is_null($v->editLabelCol())){
                $v->editLabelCol(['span'=>8,]);
            }
            if(is_null($v->editWrapperCol())){
                $v->editWrapperCol(['span'=>14,]);
            }
        });
        $fieldArr=array_values($this->fields->rendGroup()->fieldToArrayPageType('edit')->toArray());

        if(is_null($this->info)){
            $this->info=new \tpScriptVueCurd\base\model\TimeModel();
        }else if(is_array($this->info)){
            $this->info=new \tpScriptVueCurd\base\model\TimeModel($this->info);
        }
        $info=$this->info->toArray();
        //只处理地区
        $this->fields->filter(fn(ModelField $v)=> ($v instanceof RegionField && $v->canEdit() === false) ||($v instanceof  FilesField))->doShowData($info);
        //原信息
        $info['sourceData']=$this->info;


        $request=request();
        $data=[
            'vueCurdVersion'=>\tpScriptVueCurd\traits\controller\Vue::vueCurdVersion(),
            'vueCurdAction'=>$request->action(),
            'vueCurdController'=>$request->controller(),
            'vueCurdModule'=>$this->app->http->getName(),
            'guid'=>$this->guid,
            'loginUrl'=>tpScriptVueCurdGetLoginUrl(),
            'vueCurdDebug'=>\think\facade\App::isDebug(),

            'jsPath'=>$this->jsPath,
            'title'=>$this->title,
            'fields'=>$fieldArr,
            'groupFields'=>$this->fields->groupItems?FieldCollection::groupListByItems($fieldArr):null,
            'info'=>$info,
            'fieldComponents'=>$this->fields->getComponents('edit'),
            'subBtnTitle'=>$this->subBtnTitle?:'确定提交',
            'refreshBtnTitle'=>$this->refreshBtnTitle,
            'saveUrl'=>$this->saveUrl,
            'bgColor'=>$this->bgColor,
        ];
        $this->app->view->assign($data);
        $this->app->view->assign('vue_data_json', json_encode($data));
        return $this->app->view->fetch(getVCurdDir().'tpl'.DIRECTORY_SEPARATOR.'single'.DIRECTORY_SEPARATOR.'edit.vue');
    }

}