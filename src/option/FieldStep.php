<?php


namespace tpScriptVueCurd\option;



use think\Collection;
use think\db\Query;
use think\Exception;
use tpScriptVueCurd\base\model\BaseModel;
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
    private $onEditShow;
    private $saveBefore;
    private $saveAfter;
    private $saveAfterCommited;
    private $authCheckAndCheckBefore;
    //FieldStepBaseConfig
    public array $config=[//一些其他配置，如颜色
        //FieldStepBaseConfig
    ];

    /**
     * 列表步骤中显示的标签
     * @var FieldStepTag[]
     */
    private array $tags=[];
    private string $remark='';//将在列表步骤中显示的备注
    private string $remarkBtnText='查看';
    private $listRowDo;
    private string $listDirectSubmit='';//列表中直接提交，不经过编辑页面，按钮提示的字段。如果有，列表中直接提交

    /**
     * 权限查询条件（满足条件时，才能显示此条数据信息，默认都能查看，多个步骤时条件是 or ）
     * @var callable $authWhere
     */
    private $authWhere;//每个步骤对角色的查询条件
//    switch($auth_id){
//        case 1:
//            $query->where(...);
//            break;
//        case 2:
//            $query->where(...);
//            break;
//    }



    /**
     * 步骤与数据关联后将会执行 function(FieldStepBaseConfig $config,BaseModel $old,BaseModel $parentInfo=null,FieldStep $step,bool $isNext){}
     * @var \Closure|null
     */
    public ?\Closure $onInfo=null;


    /**
     * FieldStep constructor.
     * @param string $step
     * @param StepCheck $checkFunc
     * @param null|FieldStepBaseConfig|array|string $titleOrCinfig
     */
    public function __construct(string $step,StepCheck $checkFunc,$titleOrCinfig=null)
    {
        $this->step=$step;
        $this->checkFunc=$checkFunc;
        if(is_object($titleOrCinfig)){
            $stepConfig=$titleOrCinfig;
        }else{
            $config=[];
            if($titleOrCinfig){
                if(is_string($titleOrCinfig)){
                    $config['title']=$titleOrCinfig;
                }else{
                    $config=$titleOrCinfig;
                }
            }
            $stepConfig=new FieldStepBaseConfig($config);
        }
        if($stepConfig->title!==''){
            $this->title=$stepConfig->title;
        }

        $this->config=vueCurdMergeArrays($this->config,$stepConfig->toArray());
        if($stepConfig->onInfo){
            $this->onInfo=$stepConfig->onInfo;
        }
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
            'remarkBtnText'=>$this->getRemarkBtnText(),
            'listDirectSubmit'=>$this->listDirectSubmit(),
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
     * @param bool|callable $beforeCheckFunc
     * @return $this
     */
    public function auth($auth,$beforeCheckFunc=null):self{
        if(is_callable($auth)){
            $this->auth=$auth;
        }else if(is_bool($auth)){
            $this->auth= static fn()=>$auth;
        }else{
            throw new \think\Exception('参数错误');
        }

        $this->setAuthCheckAndCheckBefore($beforeCheckFunc);
        return $this;
    }

    private function setAuthCheckAndCheckBefore($beforeCheckFunc): void
    {
        //如有修改，需同步修改 FieldStepBase 下 auth、beforeAuthCheckFunc相关
        if(is_callable($beforeCheckFunc)){
            $this->authCheckAndCheckBefore=$beforeCheckFunc;
        }else if(is_bool($beforeCheckFunc)){
            $this->authCheckAndCheckBefore= static fn()=>$beforeCheckFunc;
        }else if(is_null($beforeCheckFunc)){
            $this->authCheckAndCheckBefore=$this->getAuthCheckAndCheckBeforeDefVal();
        }else{
            throw new \think\Exception('参数错误');
        }
    }

    /**
     * authCheckAndCheckBefore 的默认值
     * @return \Closure
     */
    public function getAuthCheckAndCheckBeforeDefVal(){
        //如有修改，需同步修改 FieldStepBase 下 auth、beforeAuthCheckFunc相关
        return fn(BaseModel $info,BaseModel $parentInfo=null,FieldCollection $fields=null, FieldStep $step = null,$list=null)=>$this->getCheckFunc()->beforeCheck($info,$parentInfo,null,$fields,$step,$list);
    }

    /**
     * 验证是否有编辑当前步骤的权限
     * @param BaseModel|null $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param Collection|\think\model\Collection|null $list
     * @return bool
     * @throws Exception
     */
    public function authCheck(BaseModel $info,BaseModel $parentInfo=null,FieldCollection $fields=null,$list=null):bool{
        //如有修改，需同步修改 FieldStepBase 下 auth、beforeAuthCheckFunc相关
        if(!isset($this->authCheckAndCheckBefore)||is_null($this->authCheckAndCheckBefore)){
            $this->setAuthCheckAndCheckBefore(null);
        }
        $authCheckAndCheckBefore=$this->authCheckAndCheckBefore;
        if(!$authCheckAndCheckBefore($info,$parentInfo,$fields,$this,$list)){
            return false;
        }
        if(!isset($this->auth)){
            return true;
        }
        $auth=$this->auth;
        return $auth($info,$parentInfo,$fields,$this,$list);
    }


    /**
     * 编辑或添加显示时执行
     * @param $onEditShow
     * @return $this
     * @throws \think\Exception
     */
    public function onEditShow($onEditShow): self
    {
        if(is_callable($onEditShow)){
            $this->onEditShow=$onEditShow;
        }else{
            throw new \think\Exception('参数错误');
        }
        return $this;
    }


    /**
     * 编辑或添加显示时执行
     * @param array $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param bool $isStepNext
     */
    public function doOnEditShow(array $info,?BaseModel $parentInfo,FieldCollection $fields,bool $isStepNext): void
    {
        if(!isset($this->onEditShow)||is_null($this->onEditShow)){
            return;
        }
        $func=$this->onEditShow;
        $func($info,$parentInfo,$fields,$isStepNext);
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
     * @param BaseModel|null $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @return void
     */
    public function doSaveBefore(&$saveData,BaseModel $info,BaseModel $parentInfo=null,FieldCollection $fields=null): void
    {
        if(!isset($this->saveBefore)||is_null($this->saveBefore)){
            return;
        }
        $func=$this->saveBefore;
        $func($saveData,$info,$parentInfo,$fields);
    }



    /**
     * 设置步骤保存后执行
     * @param $saveAfter
     * @return $this
     * @throws \think\Exception
     */
    public function saveAfter($saveAfter): self
    {
        if(is_callable($saveAfter)){
            $this->saveAfter=$saveAfter;
        }else if(is_bool($saveAfter)){
            $this->saveAfter=fn()=>$saveAfter;
        }else{
            throw new \think\Exception('参数错误');
        }
        return $this;
    }

    /**
     * 步骤保存后执行
     * @param $saveData
     * @param BaseModel|null $new
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param BaseModel|null $before
     * @return void
     */
    public function doSaveAfter(BaseModel $before,BaseModel $new,BaseModel $parentInfo=null,FieldCollection $fields=null,$saveData=[]): void
    {
        if(!isset($this->saveAfter)||is_null($this->saveAfter)){
            return;
        }
        $func=$this->saveAfter;
        $func($before,$new,$parentInfo,$fields,$saveData);
    }


    /**
     * 设置步骤保存后执行(Commit后)
     * @param $saveAfterCommited
     * @return $this
     * @throws \think\Exception
     */
    public function saveAfterCommited($saveAfterCommited): self
    {
        if(is_callable($saveAfterCommited)){
            $this->saveAfterCommited=$saveAfterCommited;
        }else if(is_bool($saveAfterCommited)){
            $this->saveAfterCommited=fn()=>$saveAfterCommited;
        }else{
            throw new \think\Exception('参数错误');
        }
        return $this;
    }

    /**
     * 步骤保存后执行(Commit后)
     * @param $saveData
     * @param BaseModel|null $new
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param BaseModel|null $before
     * @return void
     */
    public function doSaveAfterCommited(BaseModel $before,BaseModel $new,BaseModel $parentInfo=null,FieldCollection $fields=null,$saveData=[]): void
    {
        if(!isset($this->saveAfterCommited)||is_null($this->saveAfterCommited)){
            return;
        }
        $func=$this->saveAfterCommited;
        $func($before,$new,$parentInfo,$fields,$saveData);
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
                }

                if($v['back']===0){
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
     * 获取备注的按钮子：默认是查看
     * @return string
     */
    public function getRemarkBtnText(): string
    {
        return $this->remarkBtnText;
    }

    /**
     * 设置备注的按钮子：默认是查看
     * @param string $remarkBtnText
     */
    public function setRemarkBtnText(string $remarkBtnText): void
    {
        $this->remarkBtnText = $remarkBtnText;
    }


    /**
     * 列表数据设置执行
     * @param BaseModel $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param FieldStep|null $nextStepInfo
     * @return $this
     */
    public function listRowDo(BaseModel $info,BaseModel $parentInfo=null,FieldCollection $fields=null,?self $nextStepInfo=null):self{
        if(!isset($this->listRowDo)||is_null($this->listRowDo)){
            return $this;
        }
        $func=$this->listRowDo;
        $func($info,$parentInfo,$fields,$this,$nextStepInfo);
        return $this;
    }

    /**
     * 列表数据每条会执行此处设置的方法
     * 方法的参数  BaseModel $info,BaseModel $parentInfo=null,FieldCollection $fields=null,FieldStep $self
     * @param callable $func
     * @return $this
     */
    public function setListRowDo(callable $func):self{
        $this->listRowDo=$func;
        return $this;
    }


    /**
     * 设置或获取 $listDirectSubmit
     * @param string|null $listDirectSubmit
     * @return $this|string
     */
    public function listDirectSubmit(string $listDirectSubmit=null){
        if(is_null($listDirectSubmit)){
            return $this->listDirectSubmit;
        }

        $this->listDirectSubmit=$listDirectSubmit;
        return $this;
    }


    /**
     * 设置权限条件
     * 权限查询条件（满足条件时，才能显示此条数据信息，默认都能查看，多个步骤时条件是 or ）
     * @param string|array|callable|Query $where
     * @return $this
     */
    public function setAuthWhere($where):self{
        if(is_callable($where)){
            $this->authWhere=$where;
        }else{
            $this->authWhere= static function(Query $query)use($where){
                $query->where($where);
            };
        }
        return $this;
    }

    /**
     * 获取权限条件
     * @return callable|null
     */
    public function getAuthWhere():?callable{
        return $this->authWhere??null;
    }


    /**
     * 当步骤与数据绑定时，触发onInfo事件的执行
     * @param FieldStep|null $step
     * @param BaseModel $info
     * @param BaseModel|null $parentInfo
     * @param bool $isNext 步骤是否下一步
     * @return void
     */
    public static function doOnInfo(?self $step,BaseModel $info,?BaseModel $parentInfo,bool $isNext):void{
        if(!$step||!isset($step->onInfo)||is_null($step->onInfo)){
            return;
        }
        $config = new FieldStepBaseConfig();
        $oldConfig=$config->toArray();
        $func=$step->onInfo;
        $func($config,$info,$parentInfo,$step,$isNext);
        $newConfig=[];
        foreach ($config->toArray() as $k=>$v){
            if(json_encode($v)!==json_encode($oldConfig[$k])){
                $newConfig[$k]=$v;
            }
        }
        if($newConfig){
            $step->config=vueCurdMergeArrays($step->config,$newConfig);
        }
    }
}