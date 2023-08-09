<?php


namespace tpScriptVueCurd\option;


use think\Collection;
use think\db\Query;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use IteratorAggregate;
use Traversable;
use ArrayIterator;


/**
 * 如果字段步骤过于复杂，请使用此类（继承）
 *
 * 如果不使用此类的话，就要这么写（查看本页面中的注释 ： 不使用此类的步骤写法）
 *
 * Class FieldStepBase
 * @package tpScriptVueCurd\option
 */
abstract class FieldStepBase implements IteratorAggregate
{

    protected FieldCollection $fields;
    protected FieldStep $step;
    protected string $listDirectSubmit = '';


    /**
     * FieldStepBase constructor.
     * @param ModelField[] $fields
     * @throws \think\Exception
     */
    public function __construct(array $fields)
    {
        $config = new FieldStepBaseConfig();
        $this->config($config);

        $this->step = FieldStep::make(static::name(),
            StepCheck::make(
                function (BaseModel $info,BaseModel $parentInfo=null,ModelField $field=null,FieldCollection $fields=null,FieldStep $step = null,$list=null) {
                    if(!$this->premiseByBeforeCheck($info,$parentInfo,$fields,$step,$list)){
                        return false;
                    }
                    $currStep = empty($info) || empty($info->id) ? '' : endStepVal($info);

                    foreach ($this->beforeCheck() as $k => $v) {
                        if (is_numeric($k)) {
                            if (is_string($v)) {
                                if (is_subclass_of($v, self::class)) {
                                    $name = $v::name();
                                } else {
                                    throw new \think\Exception(static::class . '步骤配置不合法' . $k . '=>' . $v);
                                }
                                $v = true;
                            } else if (is_bool($v)||is_callable($v)) {
                                $name = '';
                            } else {
                                //不合法，跳过
                                continue;
                            }
                        } else if ($k === '') {
                            $name = $k;
                        } else if (is_subclass_of($k, self::class)) {
                            $name = $k::name();
                        } else {
                            throw new \think\Exception(static::class . '步骤配置不合法' . $k . '=>' . $v);
                        }
                        if ($name !== $currStep) {
                            continue;
                        }
                        if(is_array($v)){
                            foreach ($v as $key=>$val){
                                if(!isset($info[$key])||(!is_string($info[$key])&&!is_numeric($info[$key]))||(string)$info[$key]!==(string)$val){
                                    return false;
                                }
                            }
                            return !empty($v);
                        }
                        if (is_callable($v)) {
                            return $v($info, $parentInfo, $field);
                        }
                        if (is_bool($v)) {
                            return $v;
                        }
                        return ($v->func)($info, $parentInfo, $field);
                    }
                    return false;
                },
                fn(BaseModel $info,BaseModel $parentInfo=null,ModelField $field=null,FieldCollection $fields=null,FieldStep $step = null,$list=null)
                    => $this->premiseByCheck($info,$parentInfo,$fields,$step,$list)&&$this->check($info, $parentInfo, $field)
            ),
            $config,
        )->auth(
            fn(BaseModel $info, BaseModel $parentInfo = null, FieldCollection $fields = null, FieldStep $step = null, $list = null) => $this->auth($info, $parentInfo, $fields, $step, $list),
            function (BaseModel $info, BaseModel $parentInfo = null, FieldCollection $fields = null, FieldStep $step = null, $list = null) {
                $fn = $step->getAuthCheckAndCheckBeforeDefVal();
                if ($fn($info, $parentInfo, $fields,$step,$list)) {
                    $step->config['canEditReturn'] = null;
                    return true;
                }
                $step->config['canEditReturn'] = $this->canEdit($info, $parentInfo, $fields);
                return $step->config['canEditReturn'];
            },
        )->setListRowDo(
            fn(BaseModel $info, ?BaseModel $parentInfo, FieldCollection $fields, FieldStep $step,?FieldStep $nextStepInfo) => $this->listRowDo($info, $parentInfo, $fields, $step,$nextStepInfo)
        )->saveBefore(
            fn(&$saveData, BaseModel $info, BaseModel $parentInfo = null, FieldCollection $fields = null) => $this->saveBefore($saveData, $info, $parentInfo, $fields)
        )->saveAfter(
            fn(BaseModel $before, BaseModel $new, BaseModel $parentInfo = null, FieldCollection $fields = null, $saveData = []) => $this->saveAfter($before, $new, $parentInfo, $fields, $saveData)
        )->listDirectSubmit($this->listDirectSubmit)
            ->setAuthWhere(function (Query $query) {
                $this->authWhere($query);
            });


        $this->step->getBeforeChecks = function () {
            $funcs = $this->beforeCheck();

            foreach ($funcs as $key => $value) {
                if ($value instanceof FieldStepBeforeCheck) {
                    continue;
                }
                $oldKey=$key;
                if (is_numeric($key)) {
                    if (is_string($value)) {
                        if (!is_subclass_of($value, self::class)) {
                            throw  new  \think\Exception(static::class . '步骤配置不合法' . $key . '=>' . $value);
                        }
                        $key = $value;
                        $value = true;
                    } else if (!is_bool($value)&&!is_callable($value)) {
                        throw  new  \think\Exception(static::class . '步骤配置不合法' . $key . '=>' . $value);
                    }
                } else if ($key !== '' && !is_subclass_of($key, self::class)) {
                    throw  new  \think\Exception(static::class . '步骤配置不合法' . $key . '=>' . $value);
                }
                if (is_bool($value)) {
                    $value = static fn()=>$value;
                }else if(is_array($value)){
                    $value= static function ($info)use($value){
                        foreach ($value as $key=>$val){
                            if(!isset($info[$key])||(!is_string($info[$key])&&!is_numeric($info[$key]))||(string)$info[$key]!==(string)$val){
                                return false;
                            }
                        }
                        return !empty($value);
                    };
                } else if (!is_callable($value)) {
                    throw new \think\Exception(static::class . '步骤配置不合法' . $key . '=>' . $value);
                }
                if($oldKey!==$key){
                    unset( $funcs[$oldKey]);
                }
                $funcs[$key] = FieldStepBeforeCheck::make('', $value);
            }
            return $funcs;
        };
        $this->step->stepClass = static::class;


        foreach ($fields as $v) {
            $v->steps($this->step);
        }

        $this->fields = FieldCollection::make($fields);
    }


    /**
     * 当前步骤的标识
     * @return string
     */
    public static function name(): string
    {
        //默认为当前类名
        return class_basename(static::class);
    }


    /**
     * 初始化函数
     * @return static
     */
    abstract public static function make(): self;


    /**
     * 定义config
     * @param FieldStepBaseConfig $config
     */
    abstract public function config(FieldStepBaseConfig $config): void;


    /**
     * 数据下一步是否当前步骤，判断
     * @return callable[]|FieldStepBeforeCheck[]  [stepclass=>FieldStepBeforeCheck::make('',function(BaseModel $info, BaseModel $parentInfo = null, ModelField $field = null){})]
     */
    abstract protected function beforeCheck(): array;


    /**
     * 数据是否具有提交这一步的权限（根据beforeAuthCheckFunc判断，这一步是【下一步提交】还是【当前步骤修改】）
     * @param BaseModel|null $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param FieldStep|null $step
     * @param Collection|\think\model\Collection|null $list
     * @return bool
     */
    abstract protected function auth(BaseModel $info, BaseModel $parentInfo = null, FieldCollection $fields = null, FieldStep $step = null, $list = null): bool;


    /**
     * 权限查询条件（满足条件时，才能显示此条数据信息，默认都能查看，多个步骤时条件是 or ）
     * @param Query $query
     */
    abstract protected function authWhere(Query $query): void;

    /**
     * 验证数据是否符合当前步骤
     * @param BaseModel $old
     * @param BaseModel|null $parentInfo
     * @param ModelField|null $field
     * @return mixed
     */
    public function check(BaseModel $old, BaseModel $parentInfo = null, ModelField $field = null): bool
    {
        if (empty($old->id)) {
            return false;
        }
        return eqEndStep(static::name(), $old);
    }


    /**
     * 步骤是否可以编辑
     * @param BaseModel $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @return bool
     */
    public function canEdit(BaseModel $info, BaseModel $parentInfo = null, FieldCollection $fields = null): bool
    {
        return false;
    }


    /**
     * 列表页面时，当为当前步骤时，会遍历执行此方法（子类重写）
     * @param BaseModel $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection $fields
     * @param FieldStep $step 此条数据的当前步骤，可在这里设置步骤显示的一些东西  $step->setTags([new FieldStepTag('完成','blue')]);
     * @param FieldStep|null $nextStepInfo
     * @return void
     */
    public function listRowDo(BaseModel $info, ?BaseModel $parentInfo, FieldCollection $fields, FieldStep $step,?FieldStep $nextStepInfo): void
    {

    }


    /**
     * 数据保存前会执行（子类重写）
     * @param $saveData
     * @param BaseModel|null $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     */
    public function saveBefore(&$saveData, BaseModel $info, BaseModel $parentInfo = null, FieldCollection $fields = null): void
    {

    }


    /**
     * 数据保存后会执行（子类重写）
     * @param BaseModel|null $before
     * @param BaseModel|null $new
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param array $saveData
     */
    public function saveAfter(BaseModel $before, BaseModel $new, BaseModel $parentInfo = null, FieldCollection $fields = null, $saveData = []): void
    {

    }


    final public function getFields(): FieldCollection
    {
        return $this->fields;
    }

    /**
     * beforeCheck 的前置判断
     * @param BaseModel $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param FieldStep|null $step
     * @param $list
     * @return bool
     */
    protected function premiseByBeforeCheck(BaseModel $info, BaseModel $parentInfo = null, FieldCollection $fields = null, FieldStep $step = null, $list = null):bool{
        return true;
    }

    /**
     * check 的前置判断
     * @param BaseModel $info
     * @param BaseModel|null $parentInfo
     * @param FieldCollection|null $fields
     * @param FieldStep|null $step
     * @param $list
     * @return bool
     */
    protected function premiseByCheck(BaseModel $info, BaseModel $parentInfo = null, FieldCollection $fields = null, FieldStep $step = null, $list = null):bool{
        return true;
    }


    //IteratorAggregate
    #[\ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getFields()->all());
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
        })->setListRowDo(function(BaseModel $info,?BaseModel $parentInfo,FieldCollection $fields,FieldStep $self){
            //当前列表中步骤时执行
            $self->setTags([new FieldStepTag(...$info->project_approval_leader_result==1?['通过','success']:['不通过','error'])]);
        })->saveBefore(function(&$saveData,BaseModel $info){
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