<?php

namespace tpScriptVueCurd\traits\controller;

use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\field\FilesField;
use tpScriptVueCurd\field\RegionField;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;

/**
 * @property BaseModel $md
 */
trait BaseEdit
{
    protected bool $autoStepNext=false;

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
     * #title 添加与修改
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit(){
        return $this->editFields();
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
     * @param FieldCollection|null $fields  要更改的字段信息
     * @param BaseModel|null $model      更改到的模型
     * @param BaseModel|null $baseModel  父表
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function editFields(FieldCollection $fields=null,BaseModel $model=null,BaseModel $baseModel=null){
        if(is_null($fields)){
            $fields=$this->fields;
        }
        if(is_null($model)){
            $model=$this->md;
        }
        if(is_null($baseModel)&&$this->getParentController()){
            $baseModel=clone $this->getParentController()->md;
        }

        if($this->request->isAjax()){
            $data=$this->request->post();
            if(isset($this->request->editId)){
                $data['id']=$this->request->editId;
            }
            $this->setPostDataBefore($data);

            $model->startTrans();
            $savedInfo=null;
            $parentInfo=null;
            $returnSaveData=[];
            $isNext=false;
            try{
                if(empty($data['id'])){
                    $old=clone $model;
                    if($baseModel){
                        if(empty($data[$this->md::parentField()])){
                            throw new \think\Exception('缺少关键信息');
                        }
                        $parentInfo=$baseModel->find($data[$model::parentField()]);
                        if(is_null($parentInfo)){
                            throw new \think\Exception('未找到所属数据');
                        }
                    }
                    $this->editBefore($fields,$old,$parentInfo,$data);

                    $isNext=true;
                    //步骤字段
                    $fields=$fields->filterNextStepFields($old,$parentInfo,$stepInfo);
                    $fields->saveStepInfo=$stepInfo;

                    //步骤权限验证
                    if($fields->saveStepInfo&&$fields->saveStepInfo->authCheck($old,$parentInfo,$fields)===false){
                        return $this->error('您不能进行此操作-01');
                    }


                    $fields->each(function (ModelField $v)use($data,$parentInfo){$v->onEditSave($data,$old,$parentInfo);});
                    $savedInfo=$model->addInfo($data,$parentInfo,$fields,false,$returnSaveData);
                    $this->addAfter($savedInfo);
                }else{
                    $old=(clone $model)->find($data['id']);
                    if($baseModel){
                        $parentInfo=$baseModel->find($old[$model::parentField()]);
                    }
                    $this->editBefore($fields,$old,$parentInfo,$data);


                    //步骤
                    $isNext=$this->autoGetSaveStepIsNext($fields,$old,$parentInfo);
                    if(is_null($isNext)){
                        return $this->error('数据不满足当前步骤');
                    }
                    if($isNext){
                        $fields=$fields->filterNextStepFields($old,$parentInfo,$stepInfo);
                    }else{
                        $fields=$fields->filterCurrentStepFields($old,$parentInfo,$stepInfo);
                    }
                    if(!$this->checkEditUrl($fields,$stepInfo)){
                        return $this->error('您不能进行此操作-061');
                    }
                    $fields->saveStepInfo=$stepInfo;


                    //步骤权限验证
                    if($fields->saveStepInfo&&$fields->saveStepInfo->authCheck($old,$parentInfo,$fields)===false){
                        return $this->error('您不能进行此操作-02');
                    }
                    $fields->each(function (ModelField $v)use($data,$parentInfo,$old){$v->onEditSave($data,$old,$parentInfo,);});
                    $savedInfo=$model->saveInfo($data,$fields,$parentInfo,$old,$returnSaveData);
                    $this->editAfter($savedInfo);
                }
            }catch (\Exception $e){
                $model->rollback();
                $this->error($e);
            }
            $model->commit();

            //提交后
            $msg=(empty($data['id'])?'添加':($isNext?'提交':'修改')).'成功';
            $this->editCommitAfter($msg,$old,$savedInfo,$parentInfo,$returnSaveData);

            $refreshList=$this->request->refreshList??false;
            if($fields->stepIsEnable()&&$fields->saveStepInfo){
                $refreshList=$fields->saveStepInfo->config['okRefreshList']??false;
            }

            $this->success($msg,[
                'data'=>$data,
                'info'=>$savedInfo,
                'baseInfo'=>$parentInfo,
                'refreshList'=> $this->treePidField?true:$refreshList,
            ]);
        }

        $id=$this->request->editId??$this->request->param('id/d');
        if($id){
            $info=(clone $model)->find($id);
            if($baseModel){
                $base_id=$info[$model::parentField()];
                $parentInfo=$baseModel->find($info[$model::parentField()]);
            }else{
                $parentInfo=null;
            }
        }else{
            $info=clone $model;
            if($baseModel){
                //可以在外面赋值
                $base_id=$this->request->get('base_id/d',0);
                $base_id||$base_id=$this->request->param('base_id/d',0);
                $base_id||$this->errorAndCode('缺少必要参数');
                $parentInfo=$baseModel->find($base_id);
            }else{
                $parentInfo=null;
            }

            if($this->treePidField&&$this->request->param('pid')){
                $info[$this->treePidField]=$this->request->param('pid');
            }
        }

        $this->editBefore($fields,$info,$parentInfo);
        if($baseModel){
            //子表的地区为父表的值
            $fields=$fields->filter(fn(ModelField $v)=>!$v instanceof RegionField);//不编辑地区
        }




        try{
            $this->createEditFetchDataBefore($fields,$info,$parentInfo);
        }catch (\Exception $e){
            return $this->error($e);
        }
        $fetchData=$this->createEditFetchData($fields,$info,$parentInfo);

        if($baseModel){
            $fetchData['baseId']=$base_id;
            $fetchData['baseInfo']=$parentInfo;
            $fetchData['parentField']=$this->md::parentField();
            $fetchData['vueCurdAction']='childEdit';
        }


        $fetchData=$id?$this->beforeEditShow($fetchData):$this->beforeAddShow($fetchData);


        return $this->showTpl('edit',$fetchData);
    }

    /**
     * 获取编辑界面显示需要的参数
     * @param FieldCollection $fields
     * @param BaseModel|null $data
     * @param BaseModel|null $baseModel
     * @return array
     * @throws \think\Exception
     */
    protected function createEditFetchData(FieldCollection $fields,BaseModel $data,BaseModel $baseModel=null){
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
        $fields->filter(fn(ModelField $v)=> ($v instanceof RegionField && $v->canEdit() === false) ||($v instanceof  FilesField))->doShowData($info);
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


        $fieldArr=array_values($fields->rendGroup()->fieldToArrayPageType('edit')->toArray());
        return [
            'title'=>$this->title,
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
     * @param $parentInfo
     * @param $returnSaveData
     */
    public function editCommitAfter(&$msg,$old,$savedInfo,$parentInfo,$returnSaveData): void
    {
        if(isset($this->fields->saveStepInfo)&&!is_null($this->fields->saveStepInfo)&&$this->fields->stepIsEnable()){
            $this->md->startTrans();
            try{
                $this->fields->saveStepInfo->doSaveAfterCommited($old,$savedInfo,$parentInfo,$this->fields,$returnSaveData);
            }catch (\Exception $e){
                $this->md->rollback();
                $msg.='，但：'.$e->getMessage();
            }
            $this->md->commit();
        }
    }


    /**
     * 自动判断是否下一步
     * @param FieldCollection $fields
     * @param BaseModel|null $info
     * @param BaseModel|null $base
     * @return bool|null
     * @throws \think\Exception
     */
    protected function autoGetSaveStepIsNext(FieldCollection $fields,BaseModel $info,?BaseModel $base):?bool{
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
                }

                return !$curUrl;
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
}