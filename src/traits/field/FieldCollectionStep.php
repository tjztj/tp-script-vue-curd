<?php


namespace tpScriptVueCurd\traits\field;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldStep;
use tpScriptVueCurd\option\FieldStepBaseConfig;

trait FieldCollectionStep
{

    private array $stepConfig=[
        'enable'=>false,
        'listShow'=>true,
        //查看页面中，判断要显示哪些字段的类型
        //【fieldSort：根据字段排序来，当前步骤到了哪一个字段那里，那么它之前的字段全部显示】
        //【dataStepHistory：根据数据存储的步骤字段来显示哪些步骤】
        //【可以自定义一个函数，参数是要显示的对象function($fields,$info,$parentInfo):void】
        'showSortSteps'=>'dataStepHistory',
        'listFixed'=>'',//列表中，列是否浮动，'left'/'right'
        'width'=>0,//0为自动
        'showFilter'=>false,//是否启用步骤筛选筛选
        'currentFilterDefaultShow'=>true,//默认显示筛选
        'nextFilterDefaultShow'=>true,//显示筛选
    ];


    /**
     * 分步配置
     * @param array|boolean $stepConfig
     * @return $this
     * @throws \think\Exception
     */
    public function step($stepConfig):self{
        if($stepConfig===false){
            $this->stepConfig['enable']=false;
            return $this;
        }
        $this->each(function(ModelField $v){
            if(!$v->steps()){
                throw new \think\Exception('字段'.$v->name().'未配置steps');
            }
        });
        if($stepConfig===true){
            $this->stepConfig['enable']=true;
            return $this;
        }
        if(!isset($stepConfig['enable'])){
            $stepConfig['enable']=true;
        }
        $this->stepConfig=vueCurdMergeArrays($this->stepConfig,$stepConfig);
        return $this;
    }


    public function stepIsEnable():bool{
        return $this->stepConfig['enable'];
    }

    public function getStepConfig():array{
        return $this->stepConfig;
    }


    /**
     * 保存数据时会用到
     * @var FieldStep|null
     */
    public ?FieldStep $saveStepInfo;


    /**
     * 根据步骤筛选相关字段
     * @param FieldStep $fieldStep
     * @param bool $isNextStep
     * @param BaseModel|null $old
     * @param BaseModel|null $parentInfo
     * @return \tpScriptVueCurd\FieldCollection
     * @throws \think\Exception
     */
    public function getFilterStepFields(FieldStep $fieldStep,bool $isNextStep,BaseModel $old,BaseModel $parentInfo=null,$list=null):self{
        $hideFields=$old?$this->getFiledHideList($old):[];
        $fields=$this->filter(function (ModelField $v)use($fieldStep,$isNextStep,$old,$parentInfo,$list){
            return $v->steps()->filter(function(FieldStep $val)use($fieldStep,$isNextStep,$v,$old,$parentInfo,$list){
                    if($val->getStep()!==$fieldStep->getStep()){
                        return false;
                    }
                    $check=$val->getFieldCheckFunc();
                    if(!$check){
                        //不需要再验证
                        return true;
                    }
                    return $isNextStep?$check->beforeCheck($old,$parentInfo,$v,$this,$val,$list):$check->check($old,$parentInfo,$v,$this,$val,$list);
                })->count()>0;
        });


        if(empty($hideFields)){
            return $fields;
        }
        foreach ($hideFields as $k=>$v){
            foreach ($v as $val){
                $arr=explode(',',$val);
                $arr<=1||array_push($hideFields[$k],...$arr);
            }
        }

        $names=$fields->column('name');
        return $fields->filter(function(ModelField $v)use($hideFields,$names){
            if(!isset($hideFields[$v->name()])){
                return true;
            }
            return !empty(array_intersect($hideFields[$v->name()],$names));
        });
    }


    /**
     * 获取数据可自行的下一个步骤
     * @param BaseModel $old
     * @param BaseModel|null $parentInfo
     * @return FieldStep|null
     * @throws \think\Exception
     */
    public function getNextStepInfo(BaseModel $old,BaseModel $parentInfo=null,$list=null):?FieldStep{
        $nextFieldStep=null;
        $this->each(function(ModelField $v)use(&$nextFieldStep,$old,$parentInfo,$list){
            $v->steps()->each(function(FieldStep $val)use($v,&$nextFieldStep,$old,$parentInfo,$list){
                if($val->getCheckFunc()->beforeCheck($old,$parentInfo,$v,$this,$val,$list)===true){
                    if(is_null($nextFieldStep)){
                        $nextFieldStep=$val;
                    }else if($nextFieldStep->getStep()!==$val->getStep()){
                        throw new \think\Exception('[配置错误]同时满足步骤'.$nextFieldStep->getTitle().' 与 '.$val->getTitle());
                    }
                }
            });
        });
        FieldStep::doOnInfo($nextFieldStep,$old,$parentInfo,true);
        return $nextFieldStep;
    }


    /**
     * 获取数据当前满足的步骤
     * @param BaseModel|null $old
     * @param BaseModel|null $parentInfo
     * @return null|FieldStep
     * @throws \think\Exception
     */
    public function getCurrentStepInfo(BaseModel $old,BaseModel $parentInfo=null,$list=null):?FieldStep{
        $currentFieldStep=null;
        $this->each(function(ModelField $v)use(&$currentFieldStep,$old,$parentInfo,$list){
            $v->steps()->each(function(FieldStep $val)use($v,&$currentFieldStep,$old,$parentInfo,$list){
                if($val->getCheckFunc()->check($old,$parentInfo,$v,$this,$val,$list)===true){
                    if(is_null($currentFieldStep)){
                        $currentFieldStep=$val;
                    }else if($currentFieldStep->getStep()!==$val->getStep()){
                        throw new \think\Exception('[配置错误]同时满足步骤'.$currentFieldStep->getTitle().' 与 '.$val->getTitle());
                    }
                }
            });
        });
        FieldStep::doOnInfo($currentFieldStep,$old,$parentInfo,false);
        return $currentFieldStep;

    }



    /**
     * 根据数据信息，过滤下一个步骤满足的字段
     * @param BaseModel $old
     * @param BaseModel|null $oldBaseInfo
     * @param FieldStep|null $stepInfo
     * @return $this|\tpScriptVueCurd\FieldCollection
     * @throws \think\Exception
     */
    public function filterNextStepFields(BaseModel $old,BaseModel $oldBaseInfo=null,&$stepInfo=null):self{
        if(!$this->stepIsEnable()){
            //未启用，不过滤
            return $this;
        }
        $stepInfo=$this->getNextStepInfo($old,$oldBaseInfo);
        if(!$stepInfo){//无符合相关的字段
            $this->items=[];
            return $this;
        }

        return $this->getFilterStepFields(
            $stepInfo,
            true,
            $old,
            $oldBaseInfo
        );
    }



    /**
     * 根据数据信息，过滤当前步骤满足的字段
     * @param BaseModel|null $old
     * @param BaseModel|null $oldBaseInfo
     * @param FieldStep|null $stepInfo
     * @return $this|\tpScriptVueCurd\FieldCollection
     * @throws \think\Exception
     */
    public function filterCurrentStepFields(BaseModel $old,BaseModel $oldBaseInfo=null,&$stepInfo=null):self{
        if(!$this->stepIsEnable()){
            //未启用，不过滤
            return $this;
        }
        $stepInfo=$this->getCurrentStepInfo($old,$oldBaseInfo);
        if(!$stepInfo){//无符合相关的字段
            $this->items=[];
            return $this;
        }

        return $this->getFilterStepFields(
            $stepInfo,
            false,
            $old,
            $oldBaseInfo
        );
    }


    /**
     * 步骤查看规则 fieldSort
     * @param BaseModel $info
     * @return $this
     * @throws \think\Exception
     */
    public function showSortStepsFieldsFieldSort(BaseModel $info,BaseModel $parentInfo=null):self{
        $stepInfo=$this->getCurrentStepInfo($info,$parentInfo);
        if(!$stepInfo){
            $this->items=[];
            return $this;
        }
        $fields=[];
        $find=false;
        foreach ($this->items as $key => $item) {
            if($item->steps()->filter(fn (FieldStep $val)=>$val->getStep()===$stepInfo->getStep())->count()>0){
                $find=true;
            }else if($find){
                break;
            }
            $fields[]=$item;
        }
        $this->items=$fields;
        return $this;
    }


    /**
     * 步骤查看规则 dataStepHistory
     * @param BaseModel $info
     * @param BaseModel|null $parentInfo
     * @return $this
     * @throws \think\Exception
     */
    public function showSortStepsFieldsDataStepHistory(BaseModel $info,BaseModel $parentInfo=null):self{
        $stepInfo=$this->getCurrentStepInfo($info,$parentInfo);
        if(!$stepInfo){
            $this->items=[];
            return $this;
        }

        if($info::hasStepPastsField()&&$info[$info::getStepPastsField()]!==''){
            $stepVals=explode(',',$info[$info::getStepPastsField()]);
        }else{
            if(empty($info[$info::getStepField()])){
                $this->items=[];
                return $this;
            }
            $stepVals=getStepPasts($info[$info::getStepField()]);
            if(empty($stepVals)){
                $this->items=[];
                return $this;
            }
        }
        return $this->filter(fn(ModelField $v)=>$v->steps()->filter(fn(FieldStep $val)=>in_array((string)$val->getStep(),$stepVals,true))->count()>0);
    }


    /**
     * 获取步骤查看页面字段
     * @param BaseModel $info
     * @return \tpScriptVueCurd\FieldCollection|FieldCollectionStep
     * @throws \think\Exception
     */
    public function filterShowStepFields(BaseModel $info,BaseModel $parentInfo=null){
        if(!$this->stepIsEnable()){
            return $this;
        }
        switch ($this->getStepConfig()['showSortSteps']){
            case 'fieldSort':
                return $this->showSortStepsFieldsFieldSort($info,$parentInfo);
            case 'dataStepHistory':
                return $this->showSortStepsFieldsDataStepHistory($info,$parentInfo);
            default:
                //自定义字段的显示回调函数
                if(is_callable($this->getStepConfig()['showSortSteps'])){
                    $this->getStepConfig()['showSortSteps']($this,$info,$parentInfo);
                    return $this;
                }
                throw new \think\Exception('参数错误');
        }
    }

}