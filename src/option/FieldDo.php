<?php


namespace tpScriptVueCurd\option;


use think\Collection;
use think\db\Query;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;

class FieldDo
{
    /**
     * index页面执行完成后会执行
     * @var callable $indexRowDo
     */
    protected $indexRowDo;
    /**
     * index页面执行完成后会执行
     * @var callable $indexListDo
     */
    protected $indexListDo;

    /**
     * index页面执行完成后会执行（所有数据都处理完成后）
     * @var callable $indexRowAfterDo
     */
    protected $indexRowAfterDo;
    /**
     * index页面执行完成后会执行（所有数据都处理完成后）
     * @var callable $indexListAfterDo
     */
    protected $indexListAfterDo;

    /**
     * index页面显示前
     * @var callable $indexShowDo
     */
    protected $indexShowDo;

    /**
     * 列表条件筛选前会执行
     * @var callable $indexFilterBeforeDo
     */
    protected $indexFilterBeforeDo;


    /**
     * 在showInfoDo 之前执行
     * @var callable $showInfoBeforeDo
     */
    protected $showInfoBeforeDo;

    /**
     * show 页面会执行
     * @var callable $showInfoDo
     */
    protected $showInfoDo;

    /**
     * 在showInfoDo 之后执行
     * @var callable $showInfoAfterDo
     */
    protected $showInfoAfterDo;

    /**
     * 数据保存前
     * @var callable $saveBeforeDo
     */
    protected $saveBeforeDo;

    /**
     * 数据保存前（且已验证）
     * @var callable $saveBeforeCheckedDo
     */
    protected $saveBeforeCheckedDo;

    /**
     * 数据保存后
     * @var callable $saveAfterDo
     */
    protected $saveAfterDo;


    /**
     * 编辑时执行
     * @var callable $editShowDo
     */
    protected $editShowDo;


    /**
     * 导出时执行（列表，数据处理之前）
     * @var callable $exportListBeforeDo
     */
    protected $exportListBeforeDo;

    /**
     * 导出时执行（数据处理之前）
     * @var callable $exportBeforeDo
     */
    protected $exportBeforeDo;

    /**
     * 导出时执行（数据处理之后）
     * @var callable $exportAfterDo
     */
    protected $exportAfterDo;


    ##################################################################################################################

    /**
     * 设置列表数据展示时事件（单行）
     * @param callable $func
     * @return $this
     */
    public function setIndexRowDo(callable $func): self
    {
        $this->indexRowDo=$func;
        return $this;
    }
    /**
     * @param BaseModel $row
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doIndexRowDo(BaseModel $row,?BaseModel $base,ModelField $field): self
    {
        $func=$this->indexRowDo?? static function(){};
        $func($row,$base,$field);
        return $this;
    }
    /**
     * 列表获取到后执行 方便函数
     * @param FieldCollection $fields
     * @param Collection $list
     * @param BaseModel|null $base
     */
    public static function doIndex(FieldCollection $fields,Collection $list,?BaseModel $base):void{

        $list->each(static function(BaseModel $row)use($fields,$base){
            $fields->each(static function(ModelField $field)use($base,$row){
                foreach ($field->getFieldDoList() as $fieldDo){
                    $fieldDo->doIndexRowDo($row,$base,$field);
                }
            });
        });
        //另外再遍历，为了方便前面那个遍历后得到的东西在下面使用
        $fields->each(static function(ModelField $field)use($base,$list){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doIndexListDo($list,$base,$field);
            }
        });
    }


    ##################################################################################################################


    /**
     * 设置列表数据展示时事件（列表）
     * @param callable $func
     * @return $this
     */
    public function setIndexListDo(callable $func): self
    {
        $this->indexListDo=$func;
        return $this;
    }
    public function doIndexListDo(Collection $list,?BaseModel $base,ModelField $field): self
    {
        $func=$this->indexListDo?? static function(){};
        $func($list,$base,$field);
        return $this;
    }


    ##################################################################################################################





    ##################################################################################################################

    /**
     * 设置列表数据展示时事件（单行）
     * @param callable $func
     * @return $this
     */
    public function setIndexRowAfterDo(callable $func): self
    {
        $this->indexRowAfterDo=$func;
        return $this;
    }

    /**
     * @param array $row
     * @param ModelField $field
     * @return $this
     */
    public function doIndexRowAfterDo(array &$row,ModelField $field): self
    {
        $func=$this->indexRowAfterDo?? static function(){};
        $func($row,$field);
        return $this;
    }

    /**
     * 列表获取到后执行 方便函数
     * @param FieldCollection $fields
     * @param array $list
     */
    public static function doIndexAfter(FieldCollection $fields,array &$list):void{
        foreach ($list as $k=>$v){
            $fields->each(static function(ModelField $field)use(&$list,$k){
                foreach ($field->getFieldDoList() as $fieldDo){
                    $fieldDo->doIndexRowAfterDo($list[$k],$field);
                }
            });
        }
        //另外再遍历，为了方便前面那个遍历后得到的东西在下面使用
        $fields->each(static function(ModelField $field)use(&$list){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doIndexListAfterDo($list,$field);
            }
        });
    }


    ##################################################################################################################


    /**
     * 设置列表数据展示时事件（列表，所有数据都处理完成后）
     * @param callable $func
     * @return $this
     */
    public function setIndexListAfterDo(callable $func): self
    {
        $this->indexListAfterDo=$func;
        return $this;
    }
    public function doIndexListAfterDo(array &$list,ModelField $field): self
    {
        $func=$this->indexListAfterDo?? static function(){};
        $func($list,$field);
        return $this;
    }


    ##################################################################################################################

    /**
     * 设置列表页面显示时事件
     * @param callable $func
     * @return $this
     */
    public function setIndexShowDo(callable $func): self
    {
        $this->indexShowDo=$func;
        return $this;
    }
    public function doIndexShowDo(ModelField $field,?BaseModel &$baseModel,$controller): self
    {
        $func=$this->indexShowDo?? static function(){};
        $func($field,$baseModel,$controller);
        return $this;
    }

    public static function doIndexShow(FieldCollection $fields,?BaseModel &$baseModel,$controller):void{
        $fields->each(static function(ModelField $field)use(&$baseModel,$controller){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doIndexShowDo($field,$baseModel,$controller);
            }
        });
    }


    ##################################################################################################################

    /**
     * 设置字段列表筛选时事件
     * @param callable $func
     * @return $this
     */
    public function setIndexFilterBeforeDo(callable $func): self
    {
        $this->indexFilterBeforeDo=$func;
        return $this;
    }
    public function doIndexFilterBeforeDo(ModelField $field,Query $query,array &$filterData): self
    {
        $func=$this->indexFilterBeforeDo?? static function(){};
        $func($field,$query,$filterData);
        return $this;
    }
    public static function doIndexFilterBefore(FieldCollection $fields,Query $query,array &$filterData):void{
        $fields->each(static function(ModelField $field)use($query,&$filterData){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doIndexFilterBeforeDo($field,$query,$filterData);
            }
        });
    }



    ##################################################################################################################

    /**
     * 设置详情页字段事件
     * @param callable $func
     * @return $this
     */
    public function setShowInfoBeforeDo(callable $func): self
    {
        $this->showInfoBeforeDo=$func;
        return $this;
    }
    /**
     * @param BaseModel $info
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doShowInfoBeforeDo(BaseModel $info,?BaseModel $base,ModelField $field): self
    {
        $func=$this->showInfoBeforeDo?? static function(){};
        $func($info,$base,$field);
        return $this;
    }

    /**
     * 详情页字段钩子 方便函数
     * @param FieldCollection $fields
     * @param BaseModel $info
     * @param BaseModel|null $base
     */
    public static function doShowBefore(FieldCollection $fields,BaseModel $info,?BaseModel $base):void{
        $fields->each(static function(ModelField $field)use($base,$info){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doShowInfoBeforeDo($info,$base,$field);
            }
        });
    }


    ##################################################################################################################

    /**
     * 设置详情页字段事件
     * @param callable $func
     * @return $this
     */
    public function setShowInfoDo(callable $func): self
    {
        $this->showInfoDo=$func;
        return $this;
    }
    /**
     * @param BaseModel $info
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doShowInfoDo(BaseModel $info,?BaseModel $base,ModelField $field): self
    {
        $func=$this->showInfoDo?? static function(){};
        $func($info,$base,$field);
        return $this;
    }

    /**
     * 详情页字段钩子 方便函数
     * @param FieldCollection $fields
     * @param BaseModel $info
     * @param BaseModel|null $base
     */
    public static function doShow(FieldCollection $fields,BaseModel $info,?BaseModel $base):void{
        $fields->each(static function(ModelField $field)use($base,$info){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doShowInfoDo($info,$base,$field);
            }
        });
    }


    ##################################################################################################################

    /**
     * 设置详情页字段事件
     * @param callable $func
     * @return $this
     */
    public function setShowInfoAfterDo(callable $func): self
    {
        $this->showInfoAfterDo=$func;
        return $this;
    }

    /**
     * @param array $info
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doShowInfoAfterDo(array &$info,?BaseModel $base,ModelField $field): self
    {
        $func=$this->showInfoAfterDo?? static function(){};
        $func($info,$base,$field);
        return $this;
    }

    /**
     * 详情页字段钩子 方便函数
     * @param FieldCollection $fields
     * @param array $info
     * @param BaseModel|null $base
     */
    public static function doShowAfter(FieldCollection $fields,array &$info,?BaseModel $base):void{
        $fields->each(static function(ModelField $field)use($base,&$info){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doShowInfoAfterDo($info,$base,$field);
            }
        });
    }

    ##################################################################################################################

    /**
     * 设置数据保存前字段事件
     * @param callable $func
     * @return $this
     */
    public function setSaveBeforeDo(callable $func): self
    {
        $this->saveBeforeDo=$func;
        return $this;
    }

    /**
     * 数据保存前字段钩子
     * @param array $postData   提交上来的数据
     * @param BaseModel|null $before
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doSaveBeforeDo(array &$postData,BaseModel $before,?BaseModel $base,ModelField $field): self
    {
        $func=$this->saveBeforeDo?? static function(){};
        $func($postData,$before,$base,$field);
        return $this;
    }

    /**
     * 数据保存前字段钩子 方便函数
     * @param FieldCollection $fields
     * @param array $postData   提交上来的数据
     * @param BaseModel|null $info
     * @param BaseModel|null $base
     */
    public static function doSaveBefore(FieldCollection $fields,array &$postData,BaseModel $info,?BaseModel $base):void{
        $fields->each(static function(ModelField $field)use($base,$info,&$postData){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doSaveBeforeDo($postData,$info,$base,$field);
            }
        });
    }



    ##################################################################################################################

    /**
     * 设置数据保存前字段事件
     * @param callable $func
     * @return $this
     */
    public function setSaveBeforeCheckedDo(callable $func): self
    {
        $this->saveBeforeCheckedDo=$func;
        return $this;
    }

    /**
     * 数据保存前字段钩子
     * @param array $saveData
     * @param BaseModel|null $before
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doSaveBeforeCheckedDo(array &$saveData,BaseModel $before,?BaseModel $base,ModelField $field): self
    {
        $func=$this->saveBeforeCheckedDo?? static function(){};
        $func($saveData,$before,$base,$field);
        return $this;
    }

    /**
     * 数据保存前字段钩子 方便函数
     * @param FieldCollection $fields
     * @param array $postData   提交上来的数据
     * @param BaseModel|null $info
     * @param BaseModel|null $base
     */
    public static function doSaveBeforeChecked(FieldCollection $fields,array &$saveData,BaseModel $info,?BaseModel $base):void{
        $fields->each(static function(ModelField $field)use($base,$info,&$saveData){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doSaveBeforeCheckedDo($saveData,$info,$base,$field);
            }
        });
    }

    ##################################################################################################################

    /**
     * 设置数据保存后字段事件
     * @param callable $func
     * @return $this
     */
    public function setSaveAfterDo(callable $func): self
    {
        $this->saveAfterDo=$func;
        return $this;
    }

    /**
     * 数据保存后字段钩子
     * @param array $saveData   保存的数据
     * @param BaseModel|null $before
     * @param BaseModel $after
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doSaveAfterDo(array $saveData,BaseModel $before,BaseModel $after,?BaseModel $base,ModelField $field): self
    {
        $func=$this->saveAfterDo?? static function(){};
        $func($saveData,$before,$after,$base,$field);
        return $this;
    }

    /**
     * 数据保存后字段钩子 方便函数
     * @param FieldCollection $fields
     * @param array $saveData   保存的数据
     * @param BaseModel|null $before
     * @param BaseModel $after
     * @param BaseModel|null $base
     */
    public static function doSaveAfter(FieldCollection $fields,array $saveData,BaseModel $before,BaseModel $after,?BaseModel $base):void{
        $fields->each(static function(ModelField $field)use($base,$before,$after,$saveData){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doSaveAfterDo($saveData,$before,$after,$base,$field);
            }
        });
    }

    ##################################################################################################################

    /**
     * 设置数据编辑显示字段事件
     * @param callable $func
     * @return $this
     */
    public function setEditShowDo(callable $func): self
    {
        $this->editShowDo=$func;
        return $this;
    }

    /**
     * 数据编辑显示字段钩子
     * @param BaseModel|null $info
     * @param BaseModel|null $base
     * @param ModelField $field
     * @param bool $isStepNext
     * @return $this
     */
    public function doEditShowDo(BaseModel &$info,?BaseModel $base,ModelField $field,bool $isStepNext): self
    {
        $func=$this->editShowDo?? static function(){};
        $func($info,$base,$field,$isStepNext);
        return $this;
    }

    /**
     * 数据编辑显示字段钩子 方便函数
     * @param FieldCollection $fields
     * @param BaseModel|null $info
     * @param BaseModel|null $base
     * @param bool $isStepNext
     */
    public static function doEditShow(FieldCollection $fields,BaseModel &$info,?BaseModel $base,bool $isStepNext):void{
        $fields->each(static function(ModelField $field)use($base,&$info,$isStepNext){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doEditShowDo($info,$base,$field,$isStepNext);
            }
        });
    }


    ##################################################################################################################

    /**
     * 设置数据导出事件（数据处理之前）
     * @param callable $func
     * @return $this
     */
    public function setExportBeforeDo(callable $func): self
    {
        $this->exportBeforeDo=$func;
        return $this;
    }


    /**
     * 数据导出钩子（数据处理之前）
     * @param array $info
     * @param ModelField $field
     * @return $this
     */
    public function doExportBeforeDo(array &$info,ModelField $field):self{
        $func=$this->exportBeforeDo?? static function(){};
        $func($info,$field);
        return $this;
    }


    /**
     * 数据导出钩子 方便函数（数据处理之前）
     * @param FieldCollection $fields
     * @param array $info
     * @return void
     */
    public static function doExportBefore(FieldCollection $fields,array &$info):void{
        $fields->each(static function(ModelField $field)use(&$info){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doExportBeforeDo($info,$field);
            }
        });
    }

    

    ##################################################################################################################

    /**
     * 设置数据导出事件（数据处理之后）
     * @param callable $func
     * @return $this
     */
    public function setExportAfterDo(callable $func): self
    {
        $this->exportAfterDo=$func;
        return $this;
    }


    /**
     * 数据导出钩子（数据处理之后）
     * @param array $data
     * @param array $info
     * @param ModelField $field
     * @return $this
     */
    public function doExportAfterDo(array &$data,array $info,ModelField $field):self{
        $func=$this->exportAfterDo?? static function(){};
        $func($data,$info,$field);
        return $this;
    }


    /**
     * 数据导出钩子 方便函数（数据处理之后）
     * @param FieldCollection $fields
     * @param array $data
     * @param array $info
     * @return void
     */
    public static function doExportAfter(FieldCollection $fields,array &$data,array $info):void{
        $fields->each(static function(ModelField $field)use(&$data,$info){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doExportAfterDo($data,$info,$field);
            }
        });
    }

    ##################################################################################################################

    /**
     * 设置数据导出事件（列表，数据处理之前）
     * @param callable $func
     * @return $this
     */
    public function setExportListBeforeDo(callable $func): self
    {
        $this->exportListBeforeDo=$func;
        return $this;
    }


    /**
     * 数据导出钩子（列表，数据处理之前）
     * @param array $list
     * @param ModelField $field
     * @return $this
     */
    public function doExportListBeforeDo(array &$list,ModelField $field):self{
        $func=$this->exportListBeforeDo?? static function(){};
        $func($list,$field);
        return $this;
    }


    /**
     * 数据导出钩子 方便函数（数据处理之前）
     * @param FieldCollection $fields
     * @param array $list
     * @return void
     */
    public static function doExportListBefore(FieldCollection $fields,array &$list):void{
        $fields->each(static function(ModelField $field)use(&$list){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doExportListBeforeDo($list,$field);
            }
        });
    }



    ##################################################################################################################

}