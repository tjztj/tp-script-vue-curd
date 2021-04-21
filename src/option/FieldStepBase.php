<?php


namespace tpScriptVueCurd\option;


use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;


/**
 * 如果字段步骤过于复杂，请使用此类（继承）
 *
 * 如果不使用此类的话，就要这么写（查看本页面中的注释 ： 不使用此类的步骤写法）
 *
 * Class FieldStepBase
 * @package tpScriptVueCurd\option
 */
abstract class FieldStepBase
{

    protected FieldCollection $fields;
    protected FieldStep $step;


    /**
     * FieldStepBase constructor.
     * @param ModelField[] $fields
     * @throws \think\Exception
     */
    public function __construct(array $fields)
    {
        $config=new FieldStepBaseConfig();
        $this->config($config);

        $this->step = FieldStep::make(static::name(),
            StepCheck::make(
                fn(VueCurlModel $info = null, BaseModel $baseInfo = null, ModelField $field = null) => $this->beforeCheck($info, $baseInfo, $field),
                fn(VueCurlModel $info = null, BaseModel $baseInfo = null, ModelField $field = null) => $this->check($info, $baseInfo, $field)
            ),
            $config,
        )->auth(
            fn(VueCurlModel $info = null, BaseModel $baseInfo = null, FieldCollection $fields = null) => $this->auth($info, $baseInfo, $fields),
            $this->beforeAuthCheckFunc(),
        )->setListRowDo(
            fn(VueCurlModel $info,?BaseModel $baseInfo,FieldCollection $fields,FieldStep $step)=> $this->listRowDo($info, $baseInfo, $fields,$step)
        )->saveBefore(
            fn(&$saveData,VueCurlModel $info=null,BaseModel $baseInfo=null,FieldCollection $fields=null)=> $this->saveBefore($saveData, $info,$baseInfo,$fields)
        )->saveAfter(
            fn(?VueCurlModel $before,VueCurlModel $new,BaseModel $baseInfo=null,FieldCollection $fields=null,$saveData=[])=> $this->saveAfter($before, $new,$baseInfo,$fields,$saveData)
        );

        foreach ($fields as $v){
            $v->steps($this->step);
        }

        $this->fields = FieldCollection::make($fields);
    }


    /**
     * 当前步骤的标识
     * @return string
     */
    abstract public static function name(): string;


    /**
     * 初始化函数
     * @return static
     */
    abstract public static function make(): self;


    /**
     * 定义config
     * @param FieldStepBaseConfig $config
     */
    abstract public function config(FieldStepBaseConfig $config):void;


    /**
     * 数据下一步是否当前步骤，判断
     * @param VueCurlModel|null $info
     * @param BaseModel|null $baseInfo
     * @param ModelField|null $field
     * @return bool
     */
    abstract protected function beforeCheck(VueCurlModel $info = null, BaseModel $baseInfo = null, ModelField $field = null): bool;


    /**
     * 数据是否具有提交这一步的权限（根据beforeAuthCheckFunc判断，这一步是【下一步提交】还是【当前步骤修改】）
     * @param VueCurlModel|null $info
     * @param BaseModel|null $baseInfo
     * @param FieldCollection|null $fields
     * @return bool
     */
    abstract protected function auth(VueCurlModel $info=null,BaseModel $baseInfo=null,FieldCollection $fields=null):bool;

    /**
     * 验证数据是否符合当前步骤
     * @param VueCurlModel|null $old
     * @param BaseModel|null $baseInfo
     * @param ModelField|null $field
     * @return mixed
     */
    public function check(VueCurlModel $old = null, BaseModel $baseInfo = null, ModelField $field = null): bool
    {
        if (!$old || empty($old->id)) {
            return false;
        }
        return eqEndStep(static::name(), $old);
    }


    /**
     * 默认执行authCheck前，执行beforeCheck（这样只能执行下一步，不能编辑当前步骤）
     *                                  beforeCheck=null            不可以编辑数据当前步骤
     *                                  beforeCheck=true            绿灯到authCheck （可以编辑当前步骤）
     *                                  beforeCheck=false           authCheck 返回false （不可以编辑当前步骤，也不可以编辑下一步）
     *                                  beforeCheck=function(){}    自定义
     * @return mixed|null
     */
    public function beforeAuthCheckFunc(){
        return null;
    }



    /**
     * 列表页面时，当为当前步骤时，会遍历执行此方法（子类重写）
     * @param VueCurlModel $info
     * @param BaseModel|null $baseInfo
     * @param FieldCollection $fields
     * @param FieldStep $step           此条数据的当前步骤，可在这里设置步骤显示的一些东西  $step->setTags([new FieldStepTag('完成','blue')]);
     * @return void
     */
    public function listRowDo(VueCurlModel $info,?BaseModel $baseInfo,FieldCollection $fields,FieldStep $step):void{

    }


    /**
     * 数据保存前会执行（子类重写）
     * @param $saveData
     * @param VueCurlModel|null $info
     * @param BaseModel|null $baseInfo
     * @param FieldCollection|null $fields
     */
    public function saveBefore(&$saveData,VueCurlModel $info=null,BaseModel $baseInfo=null,FieldCollection $fields=null):void{

    }


    /**
     * 数据保存后会执行（子类重写）
     * @param VueCurlModel|null $before
     * @param VueCurlModel|null $new
     * @param BaseModel|null $baseInfo
     * @param FieldCollection|null $fields
     * @param array $saveData
     */
    public function saveAfter(?VueCurlModel $before,VueCurlModel $new,BaseModel $baseInfo=null,FieldCollection $fields=null,$saveData=[]):void{

    }


    final public function getFields(): FieldCollection
    {
        return $this->fields;
    }




    /*function 不使用此类的步骤写法(){
        $step3=FieldStep::make('project_approval_leader',StepCheck::make(function (?self $info)use($step2){
            if(is_null($info)){
                return false;
            }
            //必须分管领导审核通过
            return $info->project_approval_custodian_result===1&&eqEndStep($step2,$info);
        }),[
            'title'=>'镇主要领导审核',
            'listBtnText'=>'镇领导审核',
            'listBtnOpenHeight'=>'350px',
            'listBtnOpenWidth'=>'600px',
            'listBtnClass'=>'yellow',
            'listBtnUrl'=>__url('project.project/projectApprovalLeader')
        ])->auth(function(?self $info){
            return auth('project.project/projectApprovalLeader');
        })->setListRowDo(function(VueCurlModel $info,?BaseModel $baseInfo,FieldCollection $fields,FieldStep $self){
            //当前列表中步骤时执行
            $self->setTags([new FieldStepTag(...$info->project_approval_leader_result==1?['通过','success']:['不通过','error'])]);
        })->saveBefore(function(&$saveData,VueCurlModel $info){
            if($info[ProjectConstant::ESTIMATE_CAPITAL_FIELD]<ProjectConstant::TOWN_NEED_MEETING_MIN_MONEY){
                //如果是10W以下，使用提交上来的交易方式
                $saveData[ProjectConstant::TRANSACTION_MODE_FIELD]=$info[ProjectConstant::TRANSACTION_MODE_BEFORE_FIELD];
            }
        });
        $step3Fields=array_map(static fn(ModelField $field)=>$field->steps($step3),[
            TextField::init('project_approval_leader_opinion','镇主要领导意见',$group2)->required(true)->setDefaultHideFilter(),
            RadioField::init('project_approval_leader_result','镇主要领导审核结果',$group2)->required(true)->items([1=>'通过',2=>'不通过'])->setDefaultHideFilter(),
        ]);
    }*/

}