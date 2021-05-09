<?php


namespace tpScriptVueCurd\traits\controller;


use think\helper\Str;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use think\Request;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;

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
    protected bool $autoStepNext=false;
    protected bool $dontShowTpl=false;


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
     * 步骤--是否能添加
     * @param BaseModel|null $baseInfo
     * @return bool
     * @throws \think\Exception
     */
    protected function getAuthAdd(BaseModel $baseInfo=null):bool{
        if(!$this->fields->stepIsEnable()){
            return true;
        }
        $stepInfo=$this->fields->getNextStepInfo(null,$baseInfo);
        if($stepInfo){
            $fields=$this->fields->getFilterStepFields($stepInfo,true,null,$baseInfo);
            return $fields->count()>0&&$stepInfo->authCheck(null,$baseInfo,$fields);
        }
        return false;
    }

    /**
     * 获取添加时的字段信息
     * @return FieldCollection
     * @throws \think\Exception
     */
    protected function getRowAuthAddFields(): FieldCollection
    {
        if(!$this->fields->stepIsEnable()){
            return clone $this->fields;
        }
        $stepInfo=$this->fields->getNextStepInfo();
        if($stepInfo){
            $fields=$this->fields->getFilterStepFields($stepInfo,true);
            $fields->saveStepInfo=$stepInfo;
            return $fields;
        }
        return new FieldCollection();
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

        //字段钩子
        FieldDo::doShow($fields,$data,$baseInfo);

        //控制器钩子
        if($this->model instanceof BaseChildModel){
            $this->showBefore($data,$baseInfo,$fields);
        }else{
            $this->showBefore($data,$fields);
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
            $isNext=$this->autoGetSaveStepIsNext($fields,$data,$baseModel);
            if(is_null($isNext)){
                return $this->error('数据不满足当前步骤');
            }
            if($isNext){
                $fields=$fields->filterNextStepFields($data,$baseModel,$stepInfo);
                $isStepNext=true;
            }else{
                $fields=$fields->filterCurrentStepFields($data,$baseModel,$stepInfo);
                if(!$this->checkEditUrl($fields,$stepInfo)){
                    return $this->error('您不能进行此操作-06');
                }
                $isStepNext=false;
            }

            $fields->saveStepInfo=$stepInfo;

            FieldDo::doEditShow($fields,$data,$baseModel);
            $info=$data->toArray();
            //只处理地区
            $fields->filter(fn(ModelField $v)=>in_array($v->name(),[$data::getRegionField(),$data::getRegionPidField()])&&$v->canEdit()===false)->doShowData($info);
            //原信息
            $info['sourceData']=$data;
        }else{
            $fields=$fields->filterNextStepFields(null,$baseModel,$stepInfo);
            $fields->saveStepInfo=$stepInfo;
            FieldDo::doEditShow($fields,$data,$baseModel);
            $info=null;
            $isStepNext=true;
        }



        if($data&&!empty($data->id)){
            if(!$data->checkRowAuth($fields,$baseModel,'edit')){
                return $this->error('不能修改当前数据信息');
            }
        }else if($data?!$data->checkRowAuth($fields,$baseModel,'add'):!$this->model->checkRowAuth($fields,$baseModel,'add')){
            return $this->error('不能添加此栏目信息');
        }

        if($fields->saveStepInfo&&$fields->saveStepInfo->authCheck($data,$baseModel,$fields)===false){
            return $this->error('您不能进行此操作-05');
        }

        $fieldArr=array_values($fields->rendGroup()->toArray());
        return [
            'title'=>static::getTitle(),
            'fields'=>$fieldArr,
            'groupFields'=>$fields->groupItems?FieldCollection::groupListByItems($fieldArr):null,
            'info'=>$info,
            'fieldComponents'=>$fields->getComponents('edit'),
            'isStepNext'=>$isStepNext,
            'stepInfo'=>$stepInfo?$stepInfo->toArray():null,
        ];
    }


    /**
     * 添加/编辑 已经Commit 后执行
     * @param $msg
     * @param $old
     * @param $savedInfo
     * @param $baseInfo
     * @param $returnSaveData
     */
    public function editCommitAfter(&$msg,$old,$savedInfo,$baseInfo,$returnSaveData): void
    {
        if(isset($this->fields->saveStepInfo)&&!is_null($this->fields->saveStepInfo)&&$this->fields->stepIsEnable()){
            $this->model->startTrans();
            try{
                $this->fields->saveStepInfo->doSaveAfterCommited($old,$savedInfo,$baseInfo,$this->fields,$returnSaveData);
            }catch (\Exception $e){
                $this->model->rollback();
                $msg.='，但：'.$e->getMessage();
            }
            $this->model->commit();
        }
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
        if($this->dontShowTpl){
            return $this->success($data);
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


    /**
     * 自动判断是否下一步
     * @param FieldCollection $fields
     * @param VueCurlModel|null $info
     * @param BaseModel|null $base
     * @return bool|null
     * @throws \think\Exception
     */
    protected function autoGetSaveStepIsNext(FieldCollection $fields,?VueCurlModel $info,?BaseModel $base):?bool{
        if(!$fields->stepIsEnable()){
            //未启用
            return false;
        }

        $defNext=is_null($info)||empty($info->id)||$this->getSaveStepNext();

        if($this->autoStepNext===false){
            return $defNext;
        }

        $nextFields=(clone $fields)->filterNextStepFields($info,$base,$nextStepInfo);

        if($defNext){
            return $nextFields->isEmpty()||$nextStepInfo->authCheck($info,$base,$nextFields)===false?null:true;
        }

        $currFields=(clone $fields)->filterCurrentStepFields($info,$base,$curStepInfo);

        if(is_null($nextStepInfo)||$nextFields->isEmpty()){
            return $currFields->isEmpty()||$curStepInfo->authCheck($info,$base,$currFields)===false?null:false;
        }

        if(is_null($curStepInfo)||$currFields->isEmpty()){
            return true;
        }

        if($nextStepInfo->authCheck($info,$base,$nextFields)){
            if($curStepInfo->authCheck($info,$base,$currFields)){
                throw new \think\Exception('['.$nextStepInfo->getStep().']同时满足修改与下一步，理念冲突');
            }
            return true;
        }
        if($curStepInfo->authCheck($info,$base,$currFields)){
            return false;
        }
        return null;
    }


    /**
     * 判断当前地址是否能对上
     * @param FieldCollection $fields
     * @param FieldStep|null $stepInfo
     * @return bool
     */
    protected function checkEditUrl(FieldCollection $fields,?FieldStep $stepInfo):bool{
        if($stepInfo&&!$fields->isEmpty()){
            if(!empty($stepInfo->config['listBtnUrl'])){
                if(stripos($this->request->url(),$stepInfo->config['listBtnUrl'])!==0){
                    return false;
                }
            }else if(url('edit')->build()!==$this->request->baseUrl()){
                return false;
            }
        }
        return true;
    }

    /**
     * 自动判断下一步，并执行
     * @return mixed
     */
    protected function stepEdit(){
        $this->assign('vueCurdAction','edit');
        $this->autoStepNext=true;
        return $this->edit();
    }
}