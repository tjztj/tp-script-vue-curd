<?php


namespace tpScriptVueCurd\option;



use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;

class FieldStep
{

    private string $step;
    private string $title='';
    private string $fieldName;//相关字段名称，只有在字段中使用steps方法后获取到的 steps 才会有
    private StepCheck $checkFunc;
    private StepCheck $fieldCheckFunc;
    private $auth;//用来判断是否有编辑当前步骤的权限
    private $saveBefore;
    private $authCheckAndCheckBefore;
    public array $config=[//一些其他配置，如颜色
        'color'=>null,
        'listBtnText'=>'',//如果有值，列表中将显示,
        'listBtnUrl'=>'',//地址
        'listBtnColor'=>null,
        'listBtnClass'=>'',
        'listBtnWidth'=>0,
        'listBtnOpenWidth'=>'45vw',
        'listBtnOpenHeight'=>'100vh',
    ];

    /**
     * 列表步骤中显示的标签
     * @var FieldStepTag[]
     */
    private array $tags=[];
    private string $remark='';//将在列表步骤中显示的备注
    private $listRowDo;

    public function __construct(string $step,StepCheck $checkFunc,$titleOrCinfig=null)
    {
        $this->step=$step;
        $this->checkFunc=$checkFunc;
        if(!$titleOrCinfig){
            return;
        }
        if(is_string($titleOrCinfig)){
            $this->title=$titleOrCinfig;
            return;
        }

        if(isset($titleOrCinfig['title'])){
            $this->title=$titleOrCinfig['title'];
        }

        $this->config=vueCurdMergeArrays($this->config,$titleOrCinfig);
    }


    public static function make(string $step,StepCheck $check,$titleOrCinfig=null):self{
        $check->whenEmptyCheckSetByStep($step);
        return new self($step,$check,$titleOrCinfig);
    }

    public function toArray():array{
        $tags=[];
        foreach ($this->getTages() as $v){
            $tags[]=$v->toArray();
        }

        return [
            'step'=>$this->getStep(),
            'title'=>$this->getTitle(),
            'fieldName'=>$this->getFieldName(),
            'config'=>$this->config,
            'tags'=>$tags,
            'remark'=>$this->getRemark(),
        ];
    }

    public function getStep():string{
        return $this->step;
    }
    public function getTitle():string{
        return $this->title?:$this->getStep();
    }

    public function getCheckFunc():StepCheck{
        return $this->checkFunc;
    }
    public function setTitle(string $title){
        $this->title=$title;
        return $this;
    }
    public function setFieldName(string $fieldName):self{
        $this->fieldName=$fieldName;
        return $this;
    }

    public function getFieldName(): ?string
    {
        return $this->fieldName??null;
    }

    public function getFieldCheckFunc():?StepCheck{
        return $this->fieldCheckFunc??null;
    }

    public function setFieldCheckFunc($beforeCheck=null,$check=null): self
    {
        if($beforeCheck instanceof StepCheck){
            $fieldCheckFunc=$beforeCheck;
        }else{
            if(is_null($beforeCheck)){
                $beforeCheck=function(){
                    return true;
                };
            }
            if(is_null($check)){
                $check=function(){
                    return true;
                };
            }
            $fieldCheckFunc=StepCheck::make($beforeCheck,$check);
        }
        $this->fieldCheckFunc=$fieldCheckFunc;
        return $this;
    }

    public function removeFieldData(): self
    {
        unset($this->fieldName, $this->fieldCheckFunc);
        return $this;
    }


    /**
     * 用来判断是否有编辑当前步骤的权限
     * @param bool|callable|null $auth  默认执行authCheck前，执行beforeCheck（这样只能执行下一步，不能编辑当前步骤）
     *                                  beforeCheck=true            绿灯到authCheck 可以编辑当前步骤
     *                                  beforeCheck=false           authCheck 返回false
     *                                  beforeCheck=function(){}    自定义
     * @param bool|callable $checkBefore
     * @return $this
     */
    public function auth($auth,$checkBefore=null):self{
        if(is_callable($auth)){
            $this->auth=$auth;
        }else if(is_bool($auth)){
            $this->auth= static fn()=>$auth;
        }else{
            throw new \think\Exception('参数错误');
        }

        if(is_callable($checkBefore)){
            $this->authCheckAndCheckBefore=$checkBefore;
        }else if(is_bool($checkBefore)){
            $this->authCheckAndCheckBefore= static fn()=>$checkBefore;
        }else if(is_null($checkBefore)){
            $this->authCheckAndCheckBefore=fn(VueCurlModel $info=null,BaseModel $baseInfo=null,FieldCollection $fields=null)=>$this->getCheckFunc()->beforeCheck($info,$baseInfo);
        }else{
            throw new \think\Exception('参数错误');
        }
        return $this;
    }

    /**
     * 验证是否有编辑当前步骤的权限
     * @param VueCurlModel|null $info
     * @param BaseModel|null $baseInfo
     * @param FieldCollection|null $fields
     * @return bool
     */
    public function authCheck(VueCurlModel $info=null,BaseModel $baseInfo=null,FieldCollection $fields=null):bool{
        $authCheckAndCheckBefore=$this->authCheckAndCheckBefore;
        if(!$authCheckAndCheckBefore($info,$baseInfo,$fields)){
            return false;
        }
        if(!isset($this->auth)){
            return true;
        }
        $auth=$this->auth;
        return $auth($info,$baseInfo,$fields);
    }


    /**
     * 设置步骤保存前执行
     * @param $saveBefore
     * @return $this
     * @throws \think\Exception
     */
    public function saveBefore($saveBefore): self
    {
        if(is_callable($saveBefore)){
            $this->saveBefore=$saveBefore;
        }else if(is_bool($saveBefore)){
            $this->saveBefore=fn()=>$saveBefore;
        }else{
            throw new \think\Exception('参数错误');
        }
        return $this;
    }

    /**
     * 步骤保存前执行
     * @param $saveData
     * @param VueCurlModel|null $info
     * @param BaseModel|null $baseInfo
     * @param FieldCollection|null $fields
     * @return void
     */
    public function doSaveBefore(&$saveData,VueCurlModel $info=null,BaseModel $baseInfo=null,FieldCollection $fields=null): void
    {
        if(!isset($this->saveBefore)||is_null($this->saveBefore)){
            return;
        }
        $func=$this->saveBefore;
        $func($saveData,$info,$baseInfo,$fields);
    }



    /**
     * 将当前步骤追加到原先的 json 中
     * @param null|array|string $stepsJson
     * @return string
     * @throws \think\Exception
     */
    public function getNewStepJson($stepsJson):string{
        $stepData=[
            'step'=>$this->getStep(),
            'time'=>time(),
            'user'=>staticTpScriptVueCurdGetLoginData()['id'],
            'back'=>'0',//后退步数
        ];
        if($stepsJson){
            if(is_string($stepsJson)){
                $stepsArr=json_decode($stepsJson,true);
            }else if(!is_array($stepsJson)){
                throw new \think\Exception('步骤参数错误');
            }else{
                $stepsArr=$stepsJson;
            }
            $nowStep=end($stepsArr);
            if($nowStep['step']!==$this->getStep()){
                //新的步骤
                $stepsArr[]=$stepData;
            }
        }else{
            $stepsArr=[$stepData];
        }
        return json_encode($stepsArr);
    }


    /**
     * 修正 步骤的值
     * @param string|array $stepsJson
     * @return string
     * @throws \think\Exception
     */
    public static function correctSteps($stepsJson):string{
        if(is_string($stepsJson)){
            $stepsArr=json_decode($stepsJson,true);
        }else if(!is_array($stepsJson)){
            throw new \think\Exception('步骤参数错误');
        }else{
            $stepsArr=$stepsJson;
        }

        $keyArrs=[];
        foreach ($stepsArr as $k=>$v){
            if(!in_array($v['step'],$keyArrs)){
                $keyArrs[]=$v['step'];
                $v['back']=0;
            }else{
                $findIndex=array_search($v['step'],$keyArrs);
                $v['back']=count($keyArrs)-$findIndex-1;
                if($v['back']<0){
                    continue;
                }else if($v['back']===0){
                    //已存在且步骤紧挨，代表步骤并没有改变
                    unset($stepsArr[$k]);
                    continue;
                }
                $keyArrs=array_slice($keyArrs,0,$findIndex+1);
            }
            $stepsArr[$k]=$v;
        }

        return json_encode(array_values($stepsArr));
    }


    /**
     * 获取设置的标签
     * @return FieldStepTag[]
     */
    public function getTages():array{
        return $this->tags;
    }

    /**
     * 设置标签
     * @param FieldStepTag[] $param
     * @return $this
     */
    public function setTags(array $param):self{
        $this->tags=$param;
        return $this;
    }


    /**
     * 获取设置的备注信息
     * @return string
     */
    public function getRemark():string{
        return $this->remark;
    }

    /**
     * 设置备注信息
     * @param $remark
     * @return $this
     */
    public function setRemark($remark):self{
        $this->remark=$remark;
        return $this;
    }


    /**
     * 列表数据设置执行
     * @param VueCurlModel $info
     * @param BaseModel|null $baseInfo
     * @param FieldCollection|null $fields
     * @return $this
     */
    public function listRowDo(VueCurlModel $info,BaseModel $baseInfo=null,FieldCollection $fields=null):self{
        if(!isset($this->listRowDo)||is_null($this->listRowDo)){
            return $this;
        }
        $func=$this->listRowDo;
        $func($info,$baseInfo,$fields,$this);
        return $this;
    }

    /**
     * 列表数据每条会执行此处设置的方法
     * 方法的参数  VueCurlModel $info,BaseModel $baseInfo=null,FieldCollection $fields=null,FieldStep $self
     * @param callable $func
     * @return $this
     */
    public function setListRowDo(callable $func):self{
        $this->listRowDo=$func;
        return $this;
    }

}