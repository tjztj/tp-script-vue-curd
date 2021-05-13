<?php


namespace tpScriptVueCurd\traits\field;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldStep;

trait FieldCollectionStep
{

    private array $stepConfig=[
        'enable'=>false,
        'listShow'=>true,
        //查看页面中，判断要显示哪些字段的类型
        //【fieldSort：根据字段排序来，当前步骤到了哪一个字段那里，那么它之前的字段全部显示】
        //【dataStepHistory：根据数据存储的步骤字段来显示哪些步骤】
        //【可以自定义一个函数，参数是要显示的对象function($fields,$info,$baseInfo):void】
        'showSortSteps'=>'dataStepHistory',
        'listFixed'=>'',//列表中，列是否浮动，'left'/'right'
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
     * @param VueCurlModel|null $old
     * @param BaseModel|null $baseInfo
     * @return \tpScriptVueCurd\FieldCollection
     * @throws \think\Exception
     */
    public function getFilterStepFields(FieldStep $fieldStep,bool $isNextStep,VueCurlModel $old=null,BaseModel $baseInfo=null):self{
        $hideFields=$old?$this->getFiledHideList($old):[];
        $fields=$this->filter(function (ModelField $v)use($fieldStep,$isNextStep,$old,$baseInfo){
            return $v->steps()->filter(function(FieldStep $val)use($fieldStep,$isNextStep,$v,$old,$baseInfo){
                    if($val->getStep()!==$fieldStep->getStep()){
                        return false;
                    }
                    $check=$val->getFieldCheckFunc();
                    if(!$check){
                        //不需要再验证
                        return true;
                    }
                    return $isNextStep?$check->beforeCheck($old,$baseInfo,$v):$check->check($old,$baseInfo,$v);
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
     * @param VueCurlModel|null $old
     * @param BaseModel|null $baseInfo
     * @return FieldStep|null
     * @throws \think\Exception
     */
    public function getNextStepInfo(VueCurlModel $old=null,BaseModel $baseInfo=null):?FieldStep{
        $nextFieldStep=null;
        $this->each(function(ModelField $v)use(&$nextFieldStep,$old,$baseInfo){
            $v->steps()->each(function(FieldStep $val)use($v,&$nextFieldStep,$old,$baseInfo){
                if($val->getCheckFunc()->beforeCheck($old,$baseInfo,$v)===true){
                    if(is_null($nextFieldStep)){
                        $nextFieldStep=$val;
                    }else if($nextFieldStep->getStep()!==$val->getStep()){
                        throw new \think\Exception('[配置错误]同时满足步骤'.$nextFieldStep->getTitle().' 与 '.$val->getTitle());
                    }
                }
            });
        });
        return $nextFieldStep;
    }


    /**
     * 获取数据当前满足的步骤
     * @param VueCurlModel|null $old
     * @param BaseModel|null $baseInfo
     * @return null|FieldStep
     * @throws \think\Exception
     */
    public function getCurrentStepInfo(VueCurlModel $old=null,BaseModel $baseInfo=null):?FieldStep{
        $currentFieldStep=null;
        $this->each(function(ModelField $v)use(&$currentFieldStep,$old,$baseInfo){
            $v->steps()->each(function(FieldStep $val)use($v,&$currentFieldStep,$old,$baseInfo){
                if($val->getCheckFunc()->check($old,$baseInfo,$v)===true){
                    if(is_null($currentFieldStep)){
                        $currentFieldStep=$val;
                    }else if($currentFieldStep->getStep()!==$val->getStep()){
                        throw new \think\Exception('[配置错误]同时满足步骤'.$currentFieldStep->getTitle().' 与 '.$val->getTitle());
                    }
                }
            });
        });
        return $currentFieldStep;

    }



    /**
     * 根据数据信息，过滤下一个步骤满足的字段
     * @param VueCurlModel|null $old
     * @param BaseModel|null $oldBaseInfo
     * @param FieldStep|null $stepInfo
     * @return $this|\tpScriptVueCurd\FieldCollection
     * @throws \think\Exception
     */
    public function filterNextStepFields(VueCurlModel $old=null,BaseModel $oldBaseInfo=null,&$stepInfo=null):self{
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
     * @param VueCurlModel|null $old
     * @param BaseModel|null $oldBaseInfo
     * @param FieldStep|null $stepInfo
     * @return $this|\tpScriptVueCurd\FieldCollection
     * @throws \think\Exception
     */
    public function filterCurrentStepFields(VueCurlModel $old=null,BaseModel $oldBaseInfo=null,&$stepInfo=null):self{
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
     * @param VueCurlModel $info
     * @return $this
     * @throws \think\Exception
     */
    public function showSortStepsFieldsFieldSort(VueCurlModel $info,BaseModel $baseInfo=null):self{
        $stepInfo=$this->getCurrentStepInfo($info,$baseInfo);
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
     * @param VueCurlModel $info
     * @return $this
     * @throws \think\Exception
     */
    public function showSortStepsFieldsDataStepHistory(VueCurlModel $info,BaseModel $baseInfo=null):self{
        $stepInfo=$this->getCurrentStepInfo($info,$baseInfo);
        if(!$stepInfo){
            $this->items=[];
            return $this;
        }

        if(empty($info[$info::getStepField()])){
            $this->items=[];
            return $this;
        }
        if(is_string($info[$info::getStepField()])){
            $steps=json_decode($info[$info::getStepField()],true);
            if(empty($steps)){
                $this->items=[];
                return $this;
            }
        }else{
            $steps=$info[$info::getStepField()];
        }
        $stepVals=[];
        foreach ($steps as $k=>$v){
            if(empty($v['back'])){
                $stepVals[]=$v['step'];
            }else{
                $stepVals=array_slice($stepVals,0,count($stepVals)-$v['back']);
            }
        }
        if(empty($stepVals)){
            $this->items=[];
            return $this;
        }
        return $this->filter(fn(ModelField $v)=>$v->steps()->filter(fn(FieldStep $val)=>in_array($val->getStep(),$stepVals))->count()>0);
    }


    /**
     * 获取步骤查看页面字段
     * @param VueCurlModel $info
     * @return \tpScriptVueCurd\FieldCollection|FieldCollectionStep
     * @throws \think\Exception
     */
    public function filterShowStepFields(VueCurlModel $info,BaseModel $baseInfo=null){
        if(!$this->stepIsEnable()){
            return $this;
        }
        switch ($this->getStepConfig()['showSortSteps']){
            case 'fieldSort':
                return $this->showSortStepsFieldsFieldSort($info,$baseInfo);
            case 'dataStepHistory':
                return $this->showSortStepsFieldsDataStepHistory($info,$baseInfo);
            default:
                if(is_callable($this->getStepConfig()['showSortSteps'])){
                    $this->getStepConfig()['showSortSteps']($this,$info,$baseInfo);
                    return $this;
                }
                throw new \think\Exception('参数错误');
        }
    }

}