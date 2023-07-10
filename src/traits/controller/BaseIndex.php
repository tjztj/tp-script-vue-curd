<?php


namespace tpScriptVueCurd\traits\controller;


use think\Collection;
use think\db\Query;
use think\db\Raw;
use think\facade\Cache;
use think\Request;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\field\SelectField;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\option\FieldStep;
use tpScriptVueCurd\option\FieldStepCollection;
use tpScriptVueCurd\option\FunControllerIndexData;
use tpScriptVueCurd\option\FunControllerIndexPage;
use tpScriptVueCurd\option\FunControllerListChildBtn;
use tpScriptVueCurd\option\index_row_btn\Btn;
use tpScriptVueCurd\option\index_row_btn\OpenBtn;
use tpScriptVueCurd\option\index_row_btn\RowBtn;
use tpScriptVueCurd\option\LeftCate;

/**
 * 为了方便有时候子控制器也使用
 * Trait BaseIndex
 * @property Request $request
 * @property FunControllerIndexPage $indexPageOption
 * @property FieldCollection $fields
 * @property BaseModel $md
 */
trait BaseIndex
{

    /**
     * 列表默认排序
     * @var string|Raw
     */
    public $indexDefaultOrder='id DESC';

    /**
     * #title 数据列表
     * @return mixed|\think\response\Json|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(){
        //是否有父表
        $parentInfo=null;
        try{
            $this->indexBeforeSetBase($parentInfo);
        }catch (\Exception $e){
            $this->error($e);
        }
        $childTpl=$this->request->param('child_tpl/d',0);




        if($this->request->isAjax()){
            if($this->request->param('get_left_cate/d',0)===1){
                $leftCate=$this->getLeftCate();
                $leftCate===null||$this->success($leftCate->toArray());
            }

            $model=$this->indexListModelWhere(clone $this->md,$parentInfo);
            $model=$model->order($this->getListOrder());
            $option=$this->indexListSelect($model);
            $list=$option->sourceList;

            $this->setListDataChildBaseInfo($parentInfo,$list);

            $list=$this->setListDataStep($list,$parentInfo);
            $list=$this->setListDataRowChildBtn($list);
            $list=$this->setListDataRowAuth($list,$parentInfo);



            //字段钩子
            try{
                if(is_array($parentInfo)){
                    $listArr=[];
                    foreach ($list as $v){
                        isset($listArr[$v[$this->md::parentField()]])||$listArr[$v[$this->md::parentField()]]=[];
                        $listArr[$v[$this->md::parentField()]][]=$v;
                    }
                    foreach ($listArr as $k=>$vs){
                        $cList=\think\model\Collection::make($vs);
                        $bs=$parentInfo[$k]??null;
                        $this->fields->each(function (ModelField $v)use($cList,$bs){$v->onIndexList($cList,$bs);});
                        FieldDo::doIndex($this->fields,$cList,$bs);
                    }
                }else{
                    $this->fields->each(function (ModelField $v)use($list,$parentInfo){$v->onIndexList($list,$parentInfo);});
                    FieldDo::doIndex($this->fields,$list,$parentInfo);
                }
            }catch(\Exception $e){
                $this->error($e);
            }


            /**
             * 字段自定义按钮
             */
            $list->each(function (BaseModel $v)use($list,$parentInfo,$childTpl){
                if(is_array($parentInfo)){
                    $parentData=$parentInfo[$v[$v::parentField()]]??null;
//                    $isChildPage=false;
                }else{
                    $parentData=$parentInfo;
//                    $isChildPage=!empty($parentInfo->id);
                }



                $showBtn=new OpenBtn();
                $showBtn->btnTitle='详情';
                $showBtn->modalTitle='查看 '.$this->title.' 相关信息';
                $showBtn->modalUrl=url('show',['base_id'=>$parentData&&!empty($parentData->id)?$parentData->id:0,'id'=>$v->id])->build();
                if($childTpl){
                    $showBtn->modalOffset='lt';
                }
                $v->showBtn=$showBtn;


                $editBtn=new OpenBtn();
                $editBtn->btnTitle='修改';
                $editBtn->modalTitle='修改 '.$this->title.' 相关信息';
                $editBtn->modalUrl=url('edit',['base_id'=>$parentData&&!empty($parentData->id)?$parentData->id:0,'id'=>$v->id])->build();
                if($childTpl){
                    $editBtn->modalOffset='lt';
                }
                $v->editBtn=$editBtn;


                if($this->isTreeIndex()){
                    $childAddBtn=new OpenBtn();
                    $childAddBtn->btnTitle='添加下级';
                    $childAddBtn->btnColor='#597ef7';
                    $childAddBtn->modalTitle='新增 '.$this->title;
                    $childAddBtn->modalUrl=url('edit',['base_id'=>$parentData&&!empty($parentData->id)?$parentData->id:0,'pid'=>$v->id])->build();
                    if($childTpl){
                        $childAddBtn->modalOffset='lt';
                    }
                    $v->childAddBtn=$childAddBtn;
                }



                $otherBtns=[
                    'before'=>[...$this->getListRowBeforeBtns($v,$this->fields,$parentData,$list),...$v->getListRowBeforeBtns($this->fields,$parentData,$list)],
                    'after'=>[...$this->getListRowAfterBtns($v,$this->fields,$parentData,$list),...$v->getListRowAfterBtns($this->fields,$parentData,$list)],
                ];
                foreach ($otherBtns as $k=>$vo){
                    foreach ($vo as $key=>$val){
                        $otherBtns[$k][$key]=$val->toArray();
                    }
                }
                $v->otherBtns=$otherBtns;
            });

            //字段显示处理
            $option->data=$list->toArray();
            foreach ($option->data as $k=>$v){
                $this->fields->doShowData($option->data[$k]);
            }
            $option->baseInfo=is_array($parentInfo)?null:$parentInfo;


            //控制器数据处理钩子
            try{
                $this->indexData($option);
                FieldDo::doIndexAfter($this->fields,$option->data);
                if($this->isTreeIndex()){
                    $option->data=$this->listToTree($option->data);
                }
            }catch(\Exception $e){
                $this->error($e);
            }

            $this->success($option->toArray());
        }



        try{
            $this->fields->each(function (ModelField $v)use($parentInfo){$v->onIndexShow($parentInfo);});
            //要改fields，可以直接在 indexShowBefore 里面$this->fields
            $this->indexShowBefore($parentInfo);
            //字段钩子触发
            FieldDo::doIndexShow($this->fields,$parentInfo,$this);
        }catch (\Exception $e){
            $this->error($e);
        }



        //是否显示添加按钮
        try{
            $rowAuthAdd=$this->md->checkRowAuth($this->getRowAuthAddFields(clone $this->md,$parentInfo),$parentInfo,'add');
        }catch (\Exception $e){
            $rowAuthAdd=false;
        }


        $filterFields=clone $this->fields;
        $paranFilterData=$this->request->param('filter_data','',null);
        $filterData=$paranFilterData?json_decode($paranFilterData,true):null;
        $showFilter=$this->request->param('show_filter/d',1)===1;
        $this->indexFilterBefore($filterFields,$filterData,$showFilter);
        $this->setIndexFilterAddStep($filterFields,$filterData);




        $listColumns=array_values($this->fields->listShowItems()->fieldToArrayPageType('listColumns')->toArray());
        $showTableTool=$this->request->param('show_table_tool/d',1)===1;

        $baseId=$parentInfo?$parentInfo->id:0;

        $addBtn=new OpenBtn();
        $addBtn->btnType='primary';
        $addBtn->btnTitle='新增';
        $addBtn->modalTitle='新增 '.$this->title;
        $addBtn->modalUrl=url('edit',['base_id'=>$baseId])->build();
        if($childTpl){
            $addBtn->modalOffset='lt';
        }


        $leftCate=$this->getLeftCate();
        if($childTpl&&$leftCate&&$leftCate->show&&$leftCate->paramName==='base_id'){
            $leftCate->show=false;
        }

        $data=[
            'model'=>get_class($this->md),
            'modelName'=>class_basename($this->md),
            'showCreateTime'=> (bool)$this->fields->findByName('create_time', false),
            'indexPageOption'=>$this->indexPageOption,
            'listColumns'=>$listColumns,
            'groupGroupColumns'=>$this->fields->groupItems? FieldCollection::groupListByItems($listColumns):null,//不管显示是不是一个组，只要groupItems有，列表就分组
            'defaultUrlTpl'=>url('___URL_TPL___',['base_id'=>$baseId])->build(),//防止其他情况使用
            'listUrl'=>$this->request->url(),
            'delUrl'=>url('del')->build(),
            'downExcelTplUrl'=>url('downExcelTpl',['base_id'=>$baseId])->build(),
            'importExcelTplUrl'=>url('importExcelTpl',['base_id'=>$baseId])->build(),
            'exportUrl'=>url('export',['base_id'=>$baseId])->build(),
            'addBtn'=>$addBtn->toArray(),
            'title'=>$this->title,
            'childs'=>[],//会在BaseHaveChildController中更改
            'filterConfig'=>$filterFields->getFilterShowData(),
            'filter_data'=>$filterData?:null,
            'showFilter'=>$showFilter,
            'showTableTool'=>$showTableTool,
            'tableThemIsColor'=>tableThemIsColor(),
            'cWindow'=>$this->request->param('c_window/a'),
            'childTpl'=>$childTpl,
            'showMultipleSelection'=>null,//true or false  是否显示多选框（null，根据del权限判断）
            'auth'=>[
                'add'=>true,
                'edit'=>true,
                'del'=>true,
                'importExcelTpl'=>false,
                'downExcelTpl'=>false,
                'export'=>false,
                'stepAdd'=>$this->getAuthAdd(clone $this->md,$parentInfo),
                'rowAuthAdd'=>$rowAuthAdd,
            ],
            'baseInfo'=>$parentInfo,
            'baseId'=>$baseId,
            'fieldComponents'=>$this->fields->listShowItems()->getComponents('index'),
            'filterComponents'=>$filterFields->getFilterComponents(),
            'fieldStepConfig'=>$this->fields->getStepConfig(),
            'toolTitleLeftBtns'=>array_map(static fn (Btn $v)=>$v->toArray(),$this->getToolTitleLeftBtns($this->fields,$parentInfo)),
            'toolTitleRightBtns'=>array_map(static fn (Btn $v)=>$v->toArray(),$this->getToolTitleRightBtns($this->fields,$parentInfo)),
            'toolBtnLeftBtns'=>array_map(static fn (Btn $v)=>$v->toArray(),$this->getToolBtnLeftBtns($this->fields,$parentInfo)),
            'toolBtnRightBtns'=>array_map(static fn (Btn $v)=>$v->toArray(),$this->getToolBtnRightBtns($this->fields,$parentInfo)),
            'leftCate'=>$leftCate?$leftCate->toArray():null,
        ];


        if($this->isTreeIndex()){
            //树形结构的列名
            $data['childrenColumnName']=$this->childrenColumnName;
            $data['indentSize']=$this->indentSize;
            $data['expandAllRows']=$this->expandAllRows;
            $data['isTreeIndex']=true;
        }else{
            //展示树形数据时，每层缩进的宽度，以 px 为单位
            $data['childrenColumnName']='';
            $data['indentSize']=15;
            $data['expandAllRows']=false;
            $data['isTreeIndex']=false;
        }



        if($this->getChildControllers()){
            $this->indexFetchDoChild($data);
        }

        try{
            $this->indexFetch($data);
        }catch (\Exception $e){
            $this->error($e);
        }

        return $this->showTpl('index',$data);
    }



    /**
     * BaseIndex 的列表中对$parentInfo赋值的逻辑
     * @param BaseModel|null $parentInfo
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function indexBeforeSetBase(?BaseModel &$parentInfo): void
    {
        if($parentInfo){
            //如果有了，就不设置了
            return;
        }
        if($this->getParentController() === null){
            //如果没有父表
            return;
        }
        //可以在外面赋值
        $baseId=$this->request->get('base_id/d',0);
        $baseId||$baseId=$this->request->param('base_id/d',0);
        if(empty($baseId)){
            return;
        }
        $parentInfo=(clone $this->getParentController()->md)->find($baseId);
        if(!$parentInfo){
            throw new \think\Exception('未能获取到相关父表信息');
        }
    }

    /**
     * 列表显示的where条件处理
     * @param BaseModel $model
     * @param BaseModel|null $parentInfo
     * @param string $searchIdKey 是否可根据ID搜索
     * @return Query|BaseModel
     * @throws \think\Exception
     */
    protected function indexListModelWhere(BaseModel $model,?BaseModel $parentInfo,string $searchIdKey='id')
    {
        $filterFields=clone $this->fields;
        $filterData=$filterFields->getParamFilterData();
        $showFilter=true;
        $this->indexFilterBefore($filterFields,$filterData,$showFilter);
        $this->setIndexFilterAddStep($filterFields,$filterData);

        return $model
            ->where(function (Query $query){
                $leftCate=$this->getLeftCate();
                if(!$leftCate||!$leftCate->show||$leftCate->paramName==='base_id'){
                    return;
                }
                $query->where($leftCate->where);
            })
            ->where(function (Query $query)use($parentInfo,$searchIdKey){
                $parentInfo === null || $query->where($this->md::parentField(),$parentInfo->id);
                if(!$searchIdKey){
                    return;
                }
                $idStr=$this->request->param($searchIdKey);
                if(empty($idStr)){
                    return;
                }
                $idArr=is_array($idStr)?$idStr:explode(',',$idStr);
                $idArr=array_filter($idArr);
                if(empty($idArr)){
                    return;
                }
                $query->whereIn('id',$idArr);
            })
            ->where(function(Query $query){
                //这里不应该抛出异常
                $this->indexListWhere($query);
            })
            ->where($filterFields->getFilterWhere($filterData))
            ->where(function(Query $query){
                $childFilterData=$this->request->param('childFilterData',null,null);
                if($childFilterData&&is_string($childFilterData)){
                    $childFilterData=json_decode($childFilterData,true);
                }
                if(empty($childFilterData)){
                    return [];
                }
                if($this->getChildControllers()){
                    foreach ($this->getChildModelObjs() as $childModel){
                        /**
                         * @var BaseModel $childModel
                         */
                        $type=class_basename($childModel);
                        if(!empty($childFilterData[$type])){
                            $sql=$childModel->where($childModel->fields()->getFilterWhere($childFilterData[$type]))->field($childModel::parentField())->buildSql();
                            if($sql!==$childModel->field($childModel::parentField())->buildSql()){
                                $query->whereRaw('id IN '.$sql);
                            }
                        }
                    }
                }


            })
            ->where($this->stepAuthWhere($filterFields));
    }



    /**
     * 列表所有数据的父表设置
     * @param null|array|BaseModel $oldBaseInfo
     * @param Collection|\think\model\Collection $childList
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function setListDataChildBaseInfo(&$oldBaseInfo,$childList):void{
        if(!is_null($oldBaseInfo)){
            //如果已经有了，不需要再设值
            return;
        }
        if($this->getParentController() === null){
            //如果当前没有父表
            return;
        }
        if($childList->isEmpty()){
            //没有子数据，不需要设置父表
            return;
        }

        $parentInfo=[];
        foreach ($childList as $v){
            $parentInfo[$v[$this->md::parentField()]]=null;
        }
        foreach ((clone $this->getParentController()->md)->where('id','in',array_keys($parentInfo))->select() as $val){
            $parentInfo[$val->id]=$val;
        }
        $oldBaseInfo=$parentInfo;
    }

    /**
     * 设置列表数据步骤信息
     * @param Collection|\think\model\Collection $list
     * @param null|array|BaseModel $parentInfo
     * @return Collection|\think\model\Collection
     */
    protected function setListDataStep($list,$parentInfo){
        return $list->map(function(BaseModel $info)use($parentInfo,$list){
            if(!$this->fields->stepIsEnable()){
                return $info;
            }
            if(is_array($parentInfo)){
                $parentInfo=$parentInfo[$info[$this->md::parentField()]]??null;
            }


            $stepInfo=$this->fields->getCurrentStepInfo($info,$parentInfo,$list);
            $stepInfo === null ||$stepInfo=clone $stepInfo;
            $info->stepInfo=$stepInfo?$stepInfo->toArray():null;

            $nextStepInfo=$this->fields->getNextStepInfo($info,$parentInfo,$list);
            $nextStepInfo === null || $nextStepInfo=clone $nextStepInfo;

            if($stepInfo){
                $info->stepInfo=$stepInfo->listRowDo($info,$parentInfo,$this->fields,$nextStepInfo)->toArray();
            }else{
                $info->stepInfo=null;
            }
            $info->nextStepInfo=$nextStepInfo?$nextStepInfo->toArray():null;
            $info->stepNextCanEdit=$info->nextStepInfo && $nextStepInfo->authCheck($info, $parentInfo, $this->fields->getFilterStepFields($nextStepInfo, true, $info,$parentInfo,$list),$list);

            $stepFields=$stepInfo?$this->fields->getFilterStepFields($stepInfo,false,$info,$parentInfo,$list):FieldCollection::make();
            $info->stepFields=$stepFields->column('name');
            if($stepInfo&&($nextStepInfo===null||$stepInfo->getStep()!==$nextStepInfo->getStep())){
                $info->stepCanEdit= $stepInfo->authCheck($info, $parentInfo, $stepFields,$list);
            }else{
                $info->stepCanEdit= false;
            }

            return $info;
        });
    }

    /**
     * 设置数据 子表各按钮配置
     * @param Collection|\think\model\Collection $list
     * @return Collection|\think\model\Collection
     */
    protected function setListDataRowChildBtn($list){
        return $list->map(function(BaseModel $info){
            $childBtns=[];
            foreach ($this->getChildControllers() as $childControlle){
                /* @var $childControlle Controller */
                $btn=new FunControllerListChildBtn();
                $childControlle->baseListBtnText($btn,$info);
                if(!isset($btn->url)||is_null($btn->url)){
                    $btn->url=url(
                        str_replace(['\\','._'],['.','.'],
                            parse_name(ltrim(str_replace($this->app->getNamespace().'\\controller\\','',get_class($childControlle)),'\\'))).'/index',
                        ['base_id'=>$info->id,'child_tpl'=>1,'show_filter'=>0,'c_window'=>['f'=>'auto','w'=>'50vw','h'=>'72vh']]
                    )->build();
                }
                //可能有性能问题，所以这个功能默认关闭
                if($childControlle->parentIndexShowAddAuth){
                    try{
                        $rowAuthAdd=$childControlle->md->checkRowAuth($childControlle->getRowAuthAddFields(clone $childControlle->md,$info),$info,'add');
                    }catch (\Exception $e){
                        $rowAuthAdd=false;
                    }
                    $btn->canAdd=$rowAuthAdd&&$childControlle->getAuthAdd(clone $childControlle->md,$info);
                }
                $childBtns[class_basename($childControlle->md)]=$btn;
            }
            $info->childBtns=$childBtns;
            return $info;
        });
    }

    /**
     * 设置数据 __auth 的值，是否具有增删改的权限
     * @param Collection|\think\model\Collection $list
     * @param null|array|BaseModel $parentInfo
     * @return Collection|\think\model\Collection
     */
    protected function setListDataRowAuth($list,$parentInfo){
        return $list->map(fn(BaseModel $info)=>$info->rowSetAuth($this->fields,is_array($parentInfo)?($parentInfo[$info[$this->md::parentField()]]??null):$parentInfo,['show','edit','del']));
    }

    /**
     * 如果启用了步骤，在列表筛选中会出现当前步骤筛选
     * @param FieldCollection $filterFields
     * @param array|null $filterData
     * @throws \think\Exception
     */
    protected function setIndexFilterAddStep(FieldCollection $filterFields,?array $filterData): void
    {
        if(!$filterFields->stepIsEnable()){
            return;
        }
        $stepConfig=$filterFields->getStepConfig();
        if(!$stepConfig['showFilter']){
            return;
        }

        $steps=[];
        $filterFields->each(function (ModelField $v)use(&$steps){
            /**
             * @var FieldStepCollection $stepList
             */
            $stepList=$v->steps();
            $stepList&&$stepList->each(function (FieldStep $step)use(&$steps){
                isset($steps[$step->getStep()])||$steps[$step->getStep()]=$step->getTitle();
            });
        });

        $nextStepFieldName=$this->md::getNextStepField();
        if($nextStepFieldName&&in_array($nextStepFieldName,$this->md::getTableFields())){
            $nextStepField=SelectField::init($nextStepFieldName,'下一个步骤')->multiple(true)->items($steps);
            if($stepConfig['nextFilterDefaultShow']){
                $nextStepField->filterShow();
            }else{
                $nextStepField->filterHide();
            }
            $nextStepField->filter()->multiple(true);
            $nextStepField->pushFieldDo()->setIndexFilterBeforeDo(function (ModelField $field,Query $query,array &$filterData){
                $nextStepFieldName=$field->name();
                if(empty($filterData[$nextStepFieldName])){
                    return;
                }
                $val=$filterData[$nextStepFieldName];
                unset($filterData[$nextStepFieldName]);
                is_array($val)||$val=[$val];
                $query->whereIn($nextStepFieldName,$val);
            });

            $filterFields->unshift($nextStepField);
        }



        if($this->md::hasCurrentStepField()){
            $stepNameIsCurrentStep=true;
            $stepFieldName=$this->md::getCurrentStepField();
        }else{
            $stepNameIsCurrentStep=false;
            $stepFieldName=$this->md::getStepField();
        }


        $stepField=SelectField::init($stepFieldName,'当前步骤')->multiple(true)->items($steps);
        if($stepConfig['currentFilterDefaultShow']){
            $stepField->filterShow();
        }else{
            $stepField->filterHide();
        }
        $stepField->filter()->multiple(true);
        $stepField->pushFieldDo()->setIndexFilterBeforeDo(function (ModelField $field,Query $query,array &$filterData)use($stepNameIsCurrentStep){
            $stepFieldName=$field->name();
            if(empty($filterData[$stepFieldName])){
                return;
            }
            $val=$filterData[$stepFieldName];
            unset($filterData[$stepFieldName]);

            is_array($val)||$val=[$val];

            if($stepNameIsCurrentStep){
                $query->whereIn($stepFieldName,$val);
            }else{
                $sqls=[];
                foreach ($val as $stepVal){
                    $stepVal=str_replace(["'",'\\','"'],'',$stepVal);
                    $sqls[]="JSON_EXTRACT(`$stepFieldName`,CONCAT(\"$[\",JSON_LENGTH(`$stepFieldName` ->> '$')-1,\"].step\"))='$stepVal'";
                }
                $query->whereRaw(implode(" OR ",$sqls));
            }
        });

        $filterFields->unshift($stepField);
    }

    /**
     * @param FieldCollection $filterFields 筛选相关字段
     * @param array|null $filterData    筛选默认值
     * @param bool $showFilter  是否显示筛选
     * @return void
     */
    protected function indexFilterBefore(FieldCollection &$filterFields,?array &$filterData,bool &$showFilter):void{
        //index筛选显示前
    }


    /**
     * 步骤--是否能添加
     * @param BaseModel|null $info
     * @param BaseModel|null $parentInfo
     * @return bool
     * @throws \think\Exception
     */
    protected function getAuthAdd(BaseModel $info,BaseModel $parentInfo=null):bool{
        if(!$this->fields->stepIsEnable()){
            return true;
        }
        $stepInfo=$this->fields->getNextStepInfo($info,$parentInfo);
        if($stepInfo){
            $fields=$this->fields->getFilterStepFields($stepInfo,true,$info,$parentInfo);
            return $fields->count()>0&&$stepInfo->authCheck($info,$parentInfo,$fields);
        }
        return false;
    }

    /**
     * 获取添加时的字段信息
     * @return FieldCollection
     * @throws \think\Exception
     */
    protected function getRowAuthAddFields(BaseModel $info,BaseModel $parentInfo=null): FieldCollection
    {
        if(!$this->fields->stepIsEnable()){
            return clone $this->fields;
        }
        $stepInfo=$this->fields->getNextStepInfo($info,$parentInfo);
        if($stepInfo){
            $fields=$this->fields->getFilterStepFields($stepInfo,true,$info,$parentInfo);
            $fields->saveStepInfo=$stepInfo;
            return $fields;
        }
        return new FieldCollection();
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
     * index的列表数据查询
     * @param Query|BaseModel $model
     * @return FunControllerIndexData
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function indexListSelect($model):FunControllerIndexData{
        $indexGuid=$this->request->param('pageGuid');
        if($indexGuid){
            //记录导出url
            Cache::tag('indexExportSql')->set('indexExportSql-'.$indexGuid,(clone $model)->fetchSql(true)->select(),60*60*24);
        }

        $option=new FunControllerIndexData();
        $option->model=clone $model;
        if($this->indexPageOption->pageSize>0&&!$this->isTreeIndex()){
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


    /**
     * 获取列表排序   Raw 是为了方便重写
     * @return string|Raw
     */
    protected function getListOrder(){
        $sortField=$this->request->param('sortField');
        $sortOrder=$this->request->param('sortOrder','');
        if($sortField&&$sortOrder){
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
            $order=$this->indexDefaultOrder;
        }
        return $order;
    }


    /**
     * 是否树形列表
     * @return bool
     */
    final public function isTreeIndex(){
        return isset($this->treePidField)&&$this->treePidField!=='';
    }


    /**
     * 列表按钮组左侧
     * @param BaseModel $info               当前行信息
     * @param FieldCollection $fields
     * @param BaseModel|null $parentInfo
     * @param \think\model\Collection $list 列表信息
     * @return RowBtn[]|OpenBtn[]
     */
    public function getListRowBeforeBtns(BaseModel $info,FieldCollection $fields,?BaseModel $parentInfo,\think\model\Collection $list): array
    {
        return [];
    }

    /**
     * 列表按钮组右侧
     * @param BaseModel $info               当前行信息
     * @param FieldCollection $fields
     * @param BaseModel|null $parentInfo
     * @param \think\model\Collection $list 列表信息
     * @return RowBtn[]|OpenBtn[]
     */
    public function getListRowAfterBtns(BaseModel $info,FieldCollection $fields,?BaseModel $parentInfo,\think\model\Collection $list): array
    {
        return [];
    }

    /**
     * 工具栏标题左侧按钮
     * @param FieldCollection $fields       当前字段信息
     * @param BaseModel|null $parentInfo    父表
     * @return RowBtn[]|OpenBtn[]
     */
    public function getToolTitleLeftBtns(FieldCollection $fields,?BaseModel $parentInfo):array{
        return [];
    }


    /**
     * 工具栏标题右侧按钮
     * @param FieldCollection $fields       当前字段信息
     * @param BaseModel|null $parentInfo    父表
     * @return RowBtn[]|OpenBtn[]
     */
    public function getToolTitleRightBtns(FieldCollection $fields,?BaseModel $parentInfo):array{
        return [];
    }


    /**
     * 工具栏右侧按钮集左边
     * @param FieldCollection $fields       当前字段信息
     * @param BaseModel|null $parentInfo    父表
     * @return RowBtn[]|OpenBtn[]
     */
    public function getToolBtnLeftBtns(FieldCollection $fields,?BaseModel $parentInfo):array{
        return [];
    }


    /**
     * 工具栏右侧按钮集右边
     * @param FieldCollection $fields       当前字段信息
     * @param BaseModel|null $parentInfo    父表
     * @return RowBtn[]|OpenBtn[]
     */
    public function getToolBtnRightBtns(FieldCollection $fields,?BaseModel $parentInfo):array{
        return [];
    }


    /**
     * 获取列表查询条件（用来获取的方便方法）
     * @param BaseModel|null $model
     * @param BaseModel|null $parentInfo
     * @return Query|BaseModel
     * @throws \think\Exception
     */
    public function getModelDoListWhere(BaseModel $model=null,?BaseModel &$parentInfo=null){
        is_null($model)&&$model=$this->md;
        return $this->indexListModelWhere(clone $model,$parentInfo);
    }


    /**
     * 设置左侧分组显示
     * @return LeftCate|null
     */
    public function getLeftCate(): ?LeftCate{return null;}

}