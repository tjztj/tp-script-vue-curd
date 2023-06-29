<?php

namespace tpScriptVueCurd\option\single;

use think\App;
use think\Model;
use tpScriptVueCurd\field\FilesField;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\traits\Func;

class Show
{
    use Func;

    public string $title='';//标题
    public FieldCollection $fields;
    /**
     * @var null|Model|array
     */
    public $info=null;

    public string $bgColor='#f0f2f5';//页面背景色
    public string $padding='0';//内间距
    public string $margin='24px';//外间距



    public string $formMaxWidth='none';
    public string $formLeft='0px';


    private string $guid;
    private APP $app;
    public string $jsPath='/tpscriptvuecurd/single/show.js';



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
        $fields=$this->fields;

        $info=$this->info;
        if($info instanceof Model){
            $fields=$fields->filterHideFieldsByShow($info)->whenShowSetAttrValByWheres($info)->filterShowStepFields($info,null)->rendGroup();
            $info=$info->toArray();
        }

        $fields=$fields->filter(fn(ModelField $v)=>$v->showPage())->rendGroup();
        $fieldArr=array_values($fields->rendGroup()->fieldToArrayPageType('show')->toArray());

        if(is_null($this->info)){
            $this->info=[];
        }else if(!is_array($this->info)){
            $this->info=$this->info->toArray();
        }


        //只处理地区
        $this->fields->filter(fn(ModelField $v)=>$v instanceof  FilesField)->doShowData($info);
        //原信息
        $info['sourceData']=$this->info;


        $groupFields=$this->fields->groupItems?FieldCollection::groupListByItems($fieldArr):null;

        $this->fields->each(function (ModelField $field)use($info){
            $func=$field->getEditGridBy();
            $func&&$field->grid($func($info,null,$field));
        });
        $groupGrids=[];
        foreach ($groupFields?:[''=>$this->fields->all()] as $k=>$v){
            $func=$this->fields->getEditGridBy();
            $groupGrids[$k]=$func?$func($info,null,$v,$k):null;
        }



        $request=request();
        $data=[
            'vueCurdVersion'=>\tpScriptVueCurd\traits\controller\Vue::vueCurdVersion(),
            'vueCurdAction'=>$request->action(),
            'vueCurdController'=>$request->controller(),
            'vueCurdModule'=>$this->app->http->getName(),
            'themCssPath'=>tsvcThemCssPath(),
            'guid'=>$this->guid,
            'loginUrl'=>tpScriptVueCurdGetLoginUrl(),
            'vueCurdDebug'=>appIsDebug(),
            'groupFields'=>$groupFields,
            'groupGrids'=>$groupGrids,
            'jsPath'=>$this->jsPath,
            'title'=>$this->title,
            'fields'=>$fieldArr,
            'info'=>$info,
            'fieldComponents'=>$this->fields->getComponents('show'),
            'bgColor'=>$this->bgColor,
            'padding'=>$this->padding,
            'margin'=>$this->margin,
            'formLeft'=>$this->formLeft,
            'formMaxWidth'=>$this->formMaxWidth,
        ];
        $this->app->view->assign($data);
        $this->app->view->assign('vue_data_json', json_encode($data));
        return $this->app->view->fetch(getVCurdDir().'tpl'.DIRECTORY_SEPARATOR.'single'.DIRECTORY_SEPARATOR.'show.vue');
    }
}