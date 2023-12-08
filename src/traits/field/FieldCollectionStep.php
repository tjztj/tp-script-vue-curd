<?php

namespace tpScriptVueCurd\traits\field;

use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldStep;

trait FieldCollectionStep
{
    private array $stepConfig = [
        'enable' => false,
        'listShow' => true,
        // 查看页面中，判断要显示哪些字段的类型
        // 【fieldSort：根据字段排序来，当前步骤到了哪一个字段那里，那么它之前的字段全部显示】
        // 【dataStepHistory：根据数据存储的步骤字段来显示哪些步骤】
        // 【可以自定义一个函数，参数是要显示的对象function($fields,$info,$parentInfo):void】
        'showSortSteps' => 'dataStepHistory',
        'listFixed' => '', // 列表中，列是否浮动，'left'/'right'
        'width' => 0, // 0为自动
        'showFilter' => true, // 显示筛选
    ];

    /**
     * 分步配置.
     *
     * @param array|bool $stepConfig
     *
     * @return $this
     *
     * @throws \think\Exception
     */
    public function step($stepConfig): self
    {
        if (false === $stepConfig) {
            $this->stepConfig['enable'] = false;

            return $this;
        }
        $this->each(function (ModelField $v) {
            if (!$v->steps()) {
                throw new \think\Exception('字段'.$v->name().'未配置steps');
            }
        });
        if (true === $stepConfig) {
            $this->stepConfig['enable'] = true;

            return $this;
        }
        if (!isset($stepConfig['enable'])) {
            $stepConfig['enable'] = true;
        }
        $this->stepConfig = vueCurdMergeArrays($this->stepConfig, $stepConfig);

        return $this;
    }

    public function stepIsEnable(): bool
    {
        return $this->stepConfig['enable'];
    }

    public function getStepConfig(): array
    {
        return $this->stepConfig;
    }

    /**
     * 保存数据时会用到.
     */
    public ?FieldStep $saveStepInfo;

    /**
     * 根据步骤筛选相关字段.
     *
     * @param BaseModel|null $old
     *
     * @return \tpScriptVueCurd\FieldCollection
     *
     * @throws \think\Exception
     */
    public function getFilterStepFields(FieldStep $fieldStep, bool $isNextStep, BaseModel $old, BaseModel $parentInfo = null): self
    {
        $hideFields = $old ? $this->getFiledHideList($old) : [];
        $fields = $this->filter(function (ModelField $v) use ($fieldStep, $isNextStep, $old, $parentInfo) {
            foreach ($v->steps() as $val) {
                if ($val->getStep() !== $fieldStep->getStep()) {
                    continue;
                }
                $check = $val->getFieldCheckFunc();
                if (!$check) {
                    // 不需要再验证
                    return true;
                }
                if ($isNextStep ? $check->beforeCheck($old, $parentInfo, $v) : $check->check($old, $parentInfo, $v)) {
                    return true;
                }
            }

            return false;
        });

        if (empty($hideFields)) {
            return $fields;
        }
        foreach ($hideFields as $k => $v) {
            foreach ($v as $val) {
                $arr = explode(',', $val);
                $arr <= 1 || array_push($hideFields[$k], ...$arr);
            }
        }

        $names = $fields->column('name');

        return $fields->filter(function (ModelField $v) use ($hideFields, $names) {
            if (!isset($hideFields[$v->name()])) {
                return true;
            }

            return !empty(array_intersect($hideFields[$v->name()], $names));
        });
    }

    /**
     * 获取数据可自行的下一个步骤.
     *
     * @throws \think\Exception
     */
    public function getNextStepInfo(BaseModel $old, BaseModel $parentInfo = null): ?FieldStep
    {
        $nextFieldStep = null;
        $steps = [];
        foreach ($this->all() as $v) {
            foreach ($v->steps() as $val) {
                if (!isset($steps[$val->getStep()])) {
                    $steps[$val->getStep()] = [$v, $val];
                }
            }
        }
        foreach ($steps as $arr) {
            [$v,$val] = $arr;
            if (true === $val->getCheckFunc()->beforeCheck($old, $parentInfo, $v)) {
                if (is_null($nextFieldStep)) {
                    $nextFieldStep = $val;
                } elseif ($nextFieldStep->getStep() !== $val->getStep()) {
                    throw new \think\Exception('[配置错误]同时满足步骤'.$nextFieldStep->getTitle().' 与 '.$val->getTitle());
                }
            }
        }

        //        $this->each(function(ModelField $v)use(&$nextFieldStep,$old,$parentInfo){
        //            $v->steps()->each(function(FieldStep $val)use($v,&$nextFieldStep,$old,$parentInfo){
        //                if($val->getCheckFunc()->beforeCheck($old,$parentInfo,$v)===true){
        //                    if(is_null($nextFieldStep)){
        //                        $nextFieldStep=$val;
        //                    }else if($nextFieldStep->getStep()!==$val->getStep()){
        //                        throw new \think\Exception('[配置错误]同时满足步骤'.$nextFieldStep->getTitle().' 与 '.$val->getTitle());
        //                    }
        //                }
        //            });
        //        });
        return $nextFieldStep;
    }

    /**
     * 获取数据当前满足的步骤.
     *
     * @param BaseModel|null $old
     *
     * @throws \think\Exception
     */
    public function getCurrentStepInfo(BaseModel $old, BaseModel $parentInfo = null): ?FieldStep
    {
        $currentFieldStep = null;

        $steps = [];
        foreach ($this->all() as $v) {
            foreach ($v->steps() as $val) {
                if (!isset($steps[$val->getStep()])) {
                    $steps[$val->getStep()] = [$v, $val];
                }
            }
        }
        foreach ($steps as $arr) {
            [$v,$val] = $arr;
            if (true === $val->getCheckFunc()->check($old, $parentInfo, $v)) {
                if (is_null($currentFieldStep)) {
                    $currentFieldStep = $val;
                } elseif ($currentFieldStep->getStep() !== $val->getStep()) {
                    throw new \think\Exception('[配置错误]同时满足步骤'.$currentFieldStep->getTitle().' 与 '.$val->getTitle());
                }
            }
        }

        //        $this->each(function(ModelField $v)use(&$currentFieldStep,$old,$parentInfo){
        //            $v->steps()->each(function(FieldStep $val)use($v,&$currentFieldStep,$old,$parentInfo){
        //                if($val->getCheckFunc()->check($old,$parentInfo,$v)===true){
        //                    if(is_null($currentFieldStep)){
        //                        $currentFieldStep=$val;
        //                    }else if($currentFieldStep->getStep()!==$val->getStep()){
        //                        throw new \think\Exception('[配置错误]同时满足步骤'.$currentFieldStep->getTitle().' 与 '.$val->getTitle());
        //                    }
        //                }
        //            });
        //        });
        return $currentFieldStep;
    }

    /**
     * 根据数据信息，过滤下一个步骤满足的字段.
     *
     * @param FieldStep|null $stepInfo
     *
     * @return $this|\tpScriptVueCurd\FieldCollection
     *
     * @throws \think\Exception
     */
    public function filterNextStepFields(BaseModel $old, BaseModel $oldBaseInfo = null, &$stepInfo = null): self
    {
        if (!$this->stepIsEnable()) {
            // 未启用，不过滤
            return $this;
        }
        $stepInfo = $this->getNextStepInfo($old, $oldBaseInfo);
        if (!$stepInfo) {// 无符合相关的字段
            $this->items = [];

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
     * 根据数据信息，过滤当前步骤满足的字段.
     *
     * @param BaseModel|null $old
     * @param FieldStep|null $stepInfo
     *
     * @return $this|\tpScriptVueCurd\FieldCollection
     *
     * @throws \think\Exception
     */
    public function filterCurrentStepFields(BaseModel $old, BaseModel $oldBaseInfo = null, &$stepInfo = null): self
    {
        if (!$this->stepIsEnable()) {
            // 未启用，不过滤
            return $this;
        }
        $stepInfo = $this->getCurrentStepInfo($old, $oldBaseInfo);
        if (!$stepInfo) {// 无符合相关的字段
            $this->items = [];

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
     * 步骤查看规则 fieldSort.
     *
     * @return $this
     *
     * @throws \think\Exception
     */
    public function showSortStepsFieldsFieldSort(BaseModel $info, BaseModel $parentInfo = null): self
    {
        $stepInfo = $this->getCurrentStepInfo($info, $parentInfo);
        if (!$stepInfo) {
            $this->items = [];

            return $this;
        }
        $fields = [];
        $find = false;
        foreach ($this->items as $key => $item) {
            if ($item->steps()->filter(fn (FieldStep $val) => $val->getStep() === $stepInfo->getStep())->count() > 0) {
                $find = true;
            } elseif ($find) {
                break;
            }
            $fields[] = $item;
        }
        $this->items = $fields;

        return $this;
    }

    /**
     * 步骤查看规则 dataStepHistory.
     *
     * @return $this
     *
     * @throws \think\Exception
     */
    public function showSortStepsFieldsDataStepHistory(BaseModel $info, BaseModel $parentInfo = null): self
    {
        $stepInfo = $this->getCurrentStepInfo($info, $parentInfo);
        if (!$stepInfo) {
            $this->items = [];

            return $this;
        }

        if ($info::hasStepPastsField() && '' !== $info[$info::getStepPastsField()]) {
            $stepVals = explode(',', $info[$info::getStepPastsField()]);
        } else {
            if (empty($info[$info::getStepField()])) {
                $this->items = [];

                return $this;
            }
            $stepVals = getStepPasts($info[$info::getStepField()]);
            if (empty($stepVals)) {
                $this->items = [];

                return $this;
            }
        }

        return $this->filter(fn (ModelField $v) => $v->steps()->filter(fn (FieldStep $val) => in_array((string) $val->getStep(), $stepVals, true))->count() > 0);
    }

    /**
     * 获取步骤查看页面字段.
     *
     * @return \tpScriptVueCurd\FieldCollection|FieldCollectionStep
     *
     * @throws \think\Exception
     */
    public function filterShowStepFields(BaseModel $info, BaseModel $parentInfo = null)
    {
        if (!$this->stepIsEnable()) {
            return $this;
        }
        switch ($this->getStepConfig()['showSortSteps']) {
            case 'fieldSort':
                return $this->showSortStepsFieldsFieldSort($info, $parentInfo);
            case 'dataStepHistory':
                return $this->showSortStepsFieldsDataStepHistory($info, $parentInfo);
            default:
                if (is_callable($this->getStepConfig()['showSortSteps'])) {
                    $this->getStepConfig()['showSortSteps']($this, $info, $parentInfo);

                    return $this;
                }
                throw new \think\Exception('参数错误');
        }
    }
}
