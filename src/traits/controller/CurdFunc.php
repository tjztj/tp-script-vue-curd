<?php


namespace tpScriptVueCurd\traits\controller;


use think\helper\Str;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use think\Request;
use tpScriptVueCurd\ModelField;

/**
 * Trait CurdFunc
 * @property Request $request
 * @property VueCurlModel $model
 * @property FieldCollection $fields
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait CurdFunc
{

    public string $fetchPath;


    private bool $saveStepNext;//编辑的时候，是否是下一步
    public bool $emptySaveStepNextUseRequest=false;//如果未设置saveStepNext，是否又获取到的参数[step-next]决定
    public function getSaveStepNext():bool{
        if(!isset($this->saveStepNext)||is_null($this->saveStepNext)){
            return $this->emptySaveStepNextUseRequest&&$this->request->param('step-next/d')===1;
        }
        return $this->saveStepNext;
    }
    public function setSaveStepNext(bool $saveStepNext):self{
        $this->saveStepNext=$saveStepNext;
        return $this;
    }


    /**
     *是否能添加
     * @return bool
     */
    protected function getAuthAdd():bool{
        if(!$this->fields->stepIsEnable()){
            return true;
        }
        $stepInfo=$this->fields->getNextStepInfo();
        if($stepInfo){
            $fields=$this->fields->getFilterStepFields($stepInfo,true);
            return $fields->count()>0&&$stepInfo->authCheck(null,null,$fields);
        }else{
            return false;
        }
    }

    /**
     * #title 详细页面
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function show(){
        $id=$this->request->param('id/d');
        if(empty($id)){
            return $this->errorAndCode('缺少必要参数');
        }
        $data=$this->model->find($id);
        if(empty($data)){
            return $this->errorAndCode('未找到相关数据信息');
        }


        $baseInfo=null;
        if($this->model instanceof BaseChildModel){
            $baseInfo=$this->model::parentModelClassPath()::find($data[$this->model::parentField()]);
        }
        $fields=$this->fields->filterShowStepFields($data,$baseInfo)->filterHideFieldsByShow($data)->rendGroup();

        if($data->checkRowAuth($fields,$baseInfo,'show')===false){
            return $this->errorAndCode('您不能查看当前数据信息');
        }


        $info=$data->toArray();
        return $this->doShow(static::getTitle(),$info,$fields);
    }

    /**
     * 方便可以调用其他模型的查看页面（项目开发中可能会用到）
     * @param string $title
     * @param array $info
     * @param FieldCollection $fields
     * @return mixed
     */
    protected function doShow(string $title,array $info,FieldCollection $fields){
        $this->assign('thisAction','show');//使用它的js

        $fields=$fields->filter(fn(ModelField $v)=>$v->showPage())->rendGroup();
        $fields->doShowData($info);
        $fieldArr=array_values($fields->toArray());

        return $this->showTpl('show',$this->showFetch([
            'title'=>$title,
            'fields'=>$fieldArr,
            'groupFields'=>$fields->groupItems?FieldCollection::groupListByItems($fieldArr):null,
            'info'=>$info,
            'fieldComponents'=>$fields->getComponents('show'),
        ]));
    }



    /**
     * 获取编辑界面显示需要的参数
     * @param FieldCollection $fields
     * @param VueCurlModel|null $data
     * @param BaseModel|null $baseModel
     * @return array
     * @throws \think\Exception
     */
    protected function createEditFetchData(FieldCollection $fields,?VueCurlModel $data,BaseModel $baseModel=null){
        if($data){
            $fields=$this->getSaveStepNext()||empty($data->id)
                ?$fields->filterNextStepFields($data,$baseModel,$stepInfo)
                :$fields->filterCurrentStepFields($data,$baseModel,$stepInfo);

            $fields->saveStepInfo=$stepInfo;


            $info=$data->toArray();
            //只处理地区
            $fields->filter(fn(ModelField $v)=>in_array($v->name(),[$data::getRegionField(),$data::getRegionPidField()])&&$v->canEdit()===false)->doShowData($info);
            //原信息
            $info['sourceData']=$data;
        }else{
            $fields=$fields->filterNextStepFields(null,$baseModel,$stepInfo);
            $fields->saveStepInfo=$stepInfo;
            $info=null;
        }



        if($data&&!empty($data->id)&&!$data->checkRowAuth($fields,$baseModel,'edit')){
            return $this->error('您不能修改当前数据信息');
        }

        if($fields->saveStepInfo&&$fields->saveStepInfo->authCheck($data,$baseModel,$fields)===false){
            return $this->error('您不能进行此操作');
        }

        $fieldArr=array_values($fields->rendGroup()->toArray());
        return [
            'title'=>static::getTitle(),
            'fields'=>$fieldArr,
            'groupFields'=>$fields->groupItems?FieldCollection::groupListByItems($fieldArr):null,
            'info'=>$info,
            'fieldComponents'=>$fields->getComponents('edit'),
            'isStepNext'=>$this->getSaveStepNext(),
            'stepInfo'=>$stepInfo?$stepInfo->toArray():null,
        ];
    }


    /**
     * 删除时
     * @param VueCurlModel $model
     * @param array $ids
     * @return \think\response\Json|void
     */
    public function doDelect(VueCurlModel $model,array $ids){
        $ids=$this->beforeDel($ids);
        $list= $model->del($ids);
        $this->afterDel($list);
    }

    /**
     * 显示模板内容
     * @param string $file      直接在控制器下面的模板位置添加模板文件就可替换默认的模板，或者使用fetchPath
     * @param $data
     * @return mixed
     */
    protected function showTpl($file,$data){
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


    /**
     * 获取列表排序
     * @return string
     */
    protected function getListOrder():string{
        $sortField=$this->request->param('sortField');
        if($sortField){
            $sortOrder=$this->request->param('sortOrder','');
            switch (strtolower($sortOrder)){
                case 'desc':
                case 'asc':
                    break;
                case 'ascend':
                    $sortOrder='ASC';
                    break;
                case 'descend':
                    $sortOrder='DESC';
                    break;
                default:
                    $sortOrder='DESC';
            }
            $order=$sortField.' '.$sortOrder;
        }else{
            $order='id DESC';
        }
        return $order;
    }
}