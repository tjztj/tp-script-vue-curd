<?php


namespace tpScriptVueCurd\traits\controller;


use think\db\Query;
use think\db\Raw;
use think\helper\Str;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\field\FilesField;
use tpScriptVueCurd\FieldCollection;
use think\Request;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;
use tpScriptVueCurd\option\FunControllerIndexData;

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
     * @param FieldCollection|null $fields  要更改的字段信息
     * @param VueCurlModel|null $model      更改到的模型
     * @param VueCurlModel|null $baseModel  父表
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function editFields(FieldCollection $fields=null,VueCurlModel $model=null,VueCurlModel $baseModel=null){
        if(is_null($fields)){
            $fields=$this->fields;
        }
        if(is_null($model)){
            $model=$this->model;
        }

        if($this->request->isAjax()){
            $data=$this->request->post();
            if(isset($this->request->editId)){
                $data['id']=$this->request->editId;
            }
            $model->startTrans();
            $savedInfo=null;
            $baseInfo=null;
            $old=null;
            $returnSaveData=[];
            $isNext=false;
            try{
                if(empty($data['id'])){
                    if($baseModel){
                        if(empty($data[$this->model::parentField()])){
                            throw new \think\Exception('缺少关键信息');
                        }

                        $baseInfo=$baseModel->find($data[$model::parentField()]);
                        if(is_null($baseInfo)){
                            throw new \think\Exception('未找到所属数据');
                        }
                        $this->addBefore($data,$baseInfo);
                    }else{
                        $this->addBefore($data);
                    }


                    $isNext=true;
                    //步骤字段
                    $fields=$fields->filterNextStepFields($old,$baseInfo,$stepInfo);
                    $fields->saveStepInfo=$stepInfo;

                    //步骤权限验证
                    if($fields->saveStepInfo&&$fields->saveStepInfo->authCheck($old,$baseInfo,$fields)===false){
                        return $this->error('您不能进行此操作-01');
                    }


                    $fields->each(function (ModelField $v)use($data,$baseInfo){$v->onEditSave($data,$old,$baseInfo);});
                    if($baseModel){
                        $savedInfo=$model->addInfo($data,$baseInfo,$fields,false,$returnSaveData);
                    }else{
                        $savedInfo=$model->addInfo($data,$fields,false,$returnSaveData);
                    }

                    $this->addAfter($savedInfo);
                }else{
                    $old=$model->find($data['id']);
                    if($baseModel){
                        $baseInfo=$baseModel->find($old[$model::parentField()]);
                        $this->editBefore($data,$old,$baseInfo);
                    }else{
                        $this->editBefore($data,$old);
                    }



                    //步骤
                    $isNext=$this->autoGetSaveStepIsNext($fields,$old,$baseInfo);
                    if(is_null($isNext)){
                        return $this->error('数据不满足当前步骤');
                    }
                    if($isNext){
                        $fields=$fields->filterNextStepFields($old,$baseInfo,$stepInfo);
                    }else{
                        $fields=$fields->filterCurrentStepFields($old,$baseInfo,$stepInfo);
                    }
                    if(!$this->checkEditUrl($fields,$stepInfo)){
                        return $this->error('您不能进行此操作-061');
                    }
                    $fields->saveStepInfo=$stepInfo;


                    //步骤权限验证
                    if($fields->saveStepInfo&&$fields->saveStepInfo->authCheck($old,$baseInfo,$fields)===false){
                        return $this->error('您不能进行此操作-02');
                    }
                    $fields->each(function (ModelField $v)use($data,$baseInfo,$old){$v->onEditSave($data,$old,$baseInfo,);});
                    $savedInfo=$model->saveInfo($data,$fields,$baseInfo,$old,$returnSaveData);
                    $this->editAfter($savedInfo);
                }
            }catch (\Exception $e){
                $model->rollback();
                $this->error($e);
            }
            $model->commit();

            //提交后
            $msg=(empty($data['id'])?'添加':($isNext?'提交':'修改')).'成功';
            $this->editCommitAfter($msg,$old,$savedInfo,$baseInfo,$returnSaveData);

            $refreshList=$this->request->refreshList??false;
            if($fields->stepIsEnable()&&$fields->saveStepInfo){
                $refreshList=$fields->saveStepInfo->config['okRefreshList']??false;
            }

            $this->success($msg,[
                'data'=>$data,
                'info'=>$savedInfo,
                'baseInfo'=>$baseInfo,
                'refreshList'=> $refreshList,
            ]);
        }

        $id=$this->request->editId??$this->request->param('id/d');
        if($id){
            $info=$model->find($id);
            if($baseModel){
                $base_id=$info[$model::parentField()];
                $baseInfo=$baseModel->find($info[$model::parentField()]);
            }else{
                $baseInfo=null;
            }
        }else{
            $info=clone $model;
            if($baseModel){
                $base_id=$this->request->param('base_id/d',0);
                $base_id||$this->errorAndCode('缺少必要参数');
                $baseInfo=$baseModel->find($base_id);
            }else{
                $baseInfo=null;
            }
        }


        if($baseModel){
            //子表的地区为父表的值
            $fields=$fields->filter(fn(ModelField $v)=>!in_array($v->name(),[$model::getRegionField(),$model::getRegionPidField()])||$v->canEdit()===false);//不编辑地区
        }




        try{
            $this->createEditFetchDataBefore($fields,$info,$baseInfo);
        }catch (\Exception $e){
            return $this->error($e);
        }
        $fetchData=$this->createEditFetchData($fields,$info,$baseInfo);

        if($baseModel){
            $fetchData['baseId']=$base_id;
            $fetchData['baseInfo']=$baseInfo;
            $fetchData['parentField']=$this->model::parentField();
            $fetchData['vueCurdAction']='childEdit';
        }


        $fetchData=$id?$this->beforeEditShow($fetchData):$this->beforeAddShow($fetchData);

        return $this->showTpl('edit',$fetchData);
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
        $id=$this->request->showId??$this->request->param('id/d');
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

        try{
            //字段钩子
            FieldDo::doShowBefore($this->fields,$data,$baseInfo);
        }catch (\Exception $e){
            return $this->error($e);
        }


        $fields=$this->fields->filterHideFieldsByShow($data)->whenShowSetAttrValByWheres($data)->filterShowStepFields($data,$baseInfo)->rendGroup();


        try{
            $canShow=$data->checkRowAuth($fields,$baseInfo,'show');
        }catch (\Exception $exception){
            return $this->errorAndCode($exception->getMessage());
        }
        if($canShow===false){
            return $this->errorAndCode('您不能查看当前数据信息');
        }


        try{
            $fields->each(function (ModelField $v)use($data,$baseInfo){$v->onShow($data,$baseInfo);});
            //字段钩子
            FieldDo::doShow($fields,$data,$baseInfo);
        }catch (\Exception $e){
            return $this->error($e);
        }


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
    protected function createEditFetchData(FieldCollection $fields,VueCurlModel $data,BaseModel $baseModel=null){
        $fields->each(function (ModelField $v)use($data,$baseModel){$v->onEditShow($data,$baseModel);});

        $isStepNext=$this->autoGetSaveStepIsNext($fields,$data,$baseModel);
        if(is_null($isStepNext)){
            return $this->error('数据不满足当前步骤');
        }
        if($isStepNext){
            $fields=$fields->filterNextStepFields($data,$baseModel,$stepInfo);
        }else{
            $fields=$fields->filterCurrentStepFields($data,$baseModel,$stepInfo);
        }

        if(!$this->checkEditUrl($fields,$stepInfo)){
            return $this->error('您不能进行此操作-063');
        }

        $fields->saveStepInfo=$stepInfo;

        $sourceData=clone $data;//用来验证，防止被修改
        try{
            FieldDo::doEditShow($fields,$data,$baseModel,$isStepNext);
        }catch(\Exception $e){
            return $this->error($e);
        }



        //data可能在上面改变为有值
        $info=$data->toArray();
        //只处理地区
        $fields->filter(fn(ModelField $v)=> (in_array($v->name(), [$data::getRegionField(), $data::getRegionPidField()]) && $v->canEdit() === false) ||($v instanceof  FilesField))->doShowData($info);
        //原信息
        $info['sourceData']=$data;





        if(!empty($data->id)){
            try{
                $canEdit=$data->checkRowAuth($fields,$baseModel,'edit');
            }catch (\Exception $e){
                return $this->error($e->getMessage());
            }
            if(!$canEdit){
                return $this->error('不能修改当前数据信息');
            }
        }else{
            try{
                $canAdd=$data->checkRowAuth($fields,$baseModel,'add');
            }catch (\Exception $e){
                return $this->error($e->getMessage());
            }
            if(!$canAdd){
                return $this->error('不能添加此栏目信息');
            }
        }

        if($fields->saveStepInfo&&$fields->saveStepInfo->authCheck($sourceData,$baseModel,$fields)===false){
            return $this->error('您不能进行此操作-05');
        }

        if($fields->saveStepInfo){
            if($fields->saveStepInfo->authCheck($sourceData,$baseModel,$fields)){
                try{
                    $fields->saveStepInfo->doOnEditShow($info,$baseModel,$fields,$isStepNext);
                }catch(\Exception $e){
                    return $this->error($e);
                }
            }else{
                return $this->error('您不能进行此操作-05');
            }
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
        FilesField::setShowFileInfos();
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


    /**
     * 获取列表排序   Raw 是为了方便重写
     * @return string|Raw
     */
    protected function getListOrder(){
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


        $curStepInfo=null;
        $currFields=null;
        $currAuthCheck=null;
        if($this->autoStepNext===false){
            if($defNext){
                //如果直接下一步，那么就不管，我这里只验证能否编辑
                return $defNext;
            }
            //如果当前步骤是编辑
            //获取当前步骤信息
            $currFields=(clone $fields)->filterCurrentStepFields($info,$base,$curStepInfo);
            if(empty($curStepInfo)||$currFields->isEmpty()){
                //不能继续执行下去，所以只能返回
                return $defNext;
            }
            //通过authCheck获取canEditReturn
            $currAuthCheck= $curStepInfo->authCheck($info,$base,$currFields);
            if(!isset($curStepInfo->config['canEditReturn'])||$curStepInfo->config['canEditReturn']!==false){
                //canEditReturn可能是null或者true，这种情况，直接返回，因为true时，可以编辑。null时，不能知道是否要到下一步
                return $defNext;
            }
            $defNext=true;
        }

        $nextFields=(clone $fields)->filterNextStepFields($info,$base,$nextStepInfo);

        if($defNext){
            return $nextFields->isEmpty()||$nextStepInfo->authCheck($info,$base,$nextFields)===false?null:true;
        }

        $currFields=$currFields?:(clone $fields)->filterCurrentStepFields($info,$base,$curStepInfo);

        if(is_null($nextStepInfo)||$nextFields->isEmpty()){
            if($currFields->isEmpty()){
                return null;
            }
            if(is_null($currAuthCheck)){
                $currAuthCheck=$curStepInfo->authCheck($info,$base,$currFields);
            }
            return $currAuthCheck===false?null:false;
        }

        if(is_null($curStepInfo)||$currFields->isEmpty()){
            return true;
        }

        if($nextStepInfo->authCheck($info,$base,$nextFields)){
            if(is_null($currAuthCheck)){
                $currAuthCheck=$curStepInfo->authCheck($info,$base,$currFields);
            }
            if($currAuthCheck){
                $curUrl=$this->checkEditUrl($currFields,$curStepInfo);
                $nextUrl=$this->checkEditUrl($nextFields,$nextStepInfo);

                if($curUrl===$nextUrl){
                    throw new \think\Exception('['.$nextStepInfo->getStep().']同时满足修改与下一步，且修改与下一步执行地址都'.($curUrl?'符合':'不符合').'步骤');
                }else{
                    return !$curUrl;
                }
            }
            return true;
        }
        if(is_null($currAuthCheck)){
            $currAuthCheck=$curStepInfo->authCheck($info,$base,$currFields);
        }
        if($currAuthCheck){
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
        if($stepInfo === null){
            return true;
        }
        if(!empty($stepInfo->config['canEditActions'])){
            // dump(app('http')->getName(),$this->request->controller(),$this->request->action());
            $app=app('http')->getName();
            $app&&$app.='/';
            return (bool)array_intersect([
                $app.$this->request->controller().'/'.$this->request->action(),
                $app.str_replace('._','.',parse_name($this->request->controller())).'/'.$this->request->action(),
                $app.str_replace('._','.',parse_name($this->request->controller())).'/'.parse_name($this->request->action()),
                $app.$this->request->controller().'/'.parse_name($this->request->action()),
            ],$stepInfo->config['canEditActions']);
        }

        if(!empty($stepInfo->config['listBtnUrl'])){
            $listBtnUrlOptin=$stepInfo->config['listBtnUrl'];
            if(stripos($stepInfo->config['listBtnUrl'],'index.php')===0){
                $urlArr=explode('/',substr($stepInfo->config['listBtnUrl'],9));
            }else if(stripos($stepInfo->config['listBtnUrl'],'/index.php')===0){
                $urlArr=explode('/',substr($stepInfo->config['listBtnUrl'],10));
            }else{
                $urlArr=explode('/',$stepInfo->config['listBtnUrl']);
            }
            $urlArr=array_values(array_filter($urlArr));
            if(app('http')->getName()&&count($urlArr)>3){
                $listBtnUrlArr=[
                    $urlArr[0],$urlArr[1],current(explode('.',$urlArr[2]))
                ];
            }else{
                $listBtnUrlArr=[
                    $urlArr[0],current(explode('.',$urlArr[1]))
                ];
            }

            return stripos($this->request->url(),$stepInfo->config['listBtnUrl'])===0
                ||stripos($this->request->url(),url(implode('/',$listBtnUrlArr),[],false)->build())===0
                ||stripos($this->request->url(),url(implode('/',$listBtnUrlArr),[],true)->build())===0
                ||stripos($this->request->url(),url($stepInfo->config['listBtnUrl'],[],false)->build())===0
                ||stripos($this->request->url(),url($stepInfo->config['listBtnUrl'],[],true)->build())===0;
        }

        return url('edit')->build()===$this->request->baseUrl()
            || url('edit',[],true,true)->build()===$this->request->baseUrl();
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

    /**
     * 执行下一步
     * @return mixed
     */
    protected function nextStepEdit(){
        $this->assign('vueCurdAction','edit');
        $this->setSaveStepNext(true);
        return $this->edit();
    }



    /**
     * 编辑当前步骤
     * @return mixed
     */
    protected function currentStepEdit(){
        $this->assign('vueCurdAction','edit');
        $this->setSaveStepNext(false);
        return $this->edit();
    }




    /**
     * 数据步骤查询权限
     * 权限查询条件（满足条件时，才能显示此条数据信息，默认都能查看，多个步骤时条件是 or ）
     * @return array|\Closure
     * @throws \think\Exception
     */
    protected function stepAuthWhere(FieldCollection $fields){
        if(!$fields->stepIsEnable()){
            return [];
        }
        /**
         * @var FieldStep[] $steps
         */
        $steps=[];
        $fields->each(function(ModelField $field)use(&$steps){
            $stepList=$field->steps();
            if($stepList===null||$stepList->isEmpty()){
                return;
            }
            $stepList->each(function(FieldStep $step)use(&$steps){
                isset($steps[$step->getStep()])||$steps[$step->getStep()]=$step;
            });
        });

        return function(Query $query)use($steps){
            $query->whereOr(function (Query $query){
                $this->stepAuthWhereOr($query);
            });
            if(empty($steps)){
                return;
            }
            foreach ($steps as $v){
                $where=$v->getAuthWhere();
                if($where===null){
                    continue;
                }
                $query->whereOr($where);
            }
        };
    }

    /**
     * 步骤 or 条件，当角色不满足步骤时，却又要显示相关数据，可在此处加入条件
     * @param Query $query
     */
    protected function stepAuthWhereOr(Query $query):void{}


    /**
     * @param Query|BaseModel|BaseChildModel $model
     * @return FunControllerIndexData
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function indexListSelect($model):FunControllerIndexData{
        $option=new FunControllerIndexData();
        $option->model=clone $model;
        if($this->indexPageOption->pageSize>0){
            $pageSize=$this->indexPageOption->canGetRequestOption?$this->request->param('pageSize/d',$this->indexPageOption->pageSize):$this->indexPageOption->pageSize;
            $list=$model->paginate($pageSize);
            $option->currentPage=$list->currentPage();
            $option->lastPage=$list->lastPage();
            $option->perPage=$list->listRows();
            $option->total=$list->total();
            $option->sourceList=$list->getCollection();
        }else{
            $option->sourceList=$model->select();
            $option->total=$option->sourceList->count();
            $option->currentPage=1;
            $option->lastPage=1;
            $option->perPage=$option->total;
        }
        return $option;
    }
}