<?php
namespace tpScriptVueCurd\option\single;

use think\App;
use think\Model;
use tpScriptVueCurd\field\FilesField;
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
    public string $padding='0';//内间距
    public string $margin='24px';//外间距



    public string $formLayout='horizontal';//表单布局	'horizontal'|'vertical'|'inline'
    public string $formMaxWidth='960px';
    public string $formLeft='0px';


    private string $guid;
    private APP $app;
    public string $jsPath='/tpscriptvuecurd/single/edit.js';

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
            $this->info=[];
        }else if(!is_array($this->info)){
            $this->info=$this->info->toArray();
        }
        $info=$this->info;
        //只处理地区
        $this->fields->filter(fn(ModelField $v)=>$v instanceof  FilesField)->doShowData($info);
        //原信息
        $info['sourceData']=$this->info;

        $this->fields->each(function (ModelField $field)use($info){
            $editOnChange=$field->editOnChange();
            if($editOnChange instanceof \tpScriptVueCurd\option\field\edit_on_change\type\Func){
                throw new \think\Exception('此字段的editOnChange不能设置为function');
            }
        });


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
            'fieldComponents'=>$this->fields->getComponents('edit'),
            'subBtnTitle'=>$this->subBtnTitle?:'确定提交',
            'refreshBtnTitle'=>$this->refreshBtnTitle,
            'saveUrl'=>$this->saveUrl,
            'bgColor'=>$this->bgColor,
            'padding'=>$this->padding,
            'margin'=>$this->margin,
            'formLeft'=>$this->formLeft,
            'formLayout'=>$this->formLayout,
            'formMaxWidth'=>$this->formMaxWidth,
        ];
        $this->app->view->assign($data);
        $this->app->view->assign('vue_data_json', json_encode($data));
        return $this->app->view->fetch(getVCurdDir().'tpl'.DIRECTORY_SEPARATOR.'single'.DIRECTORY_SEPARATOR.'edit.vue');
    }

}