<?php


namespace tpScriptVueCurd\option;


use think\Collection;
use think\db\Query;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
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
     * 列表条件筛选前会执行
     * @var callable $indexFilterBeforeDo
     */
    protected $indexFilterBeforeDo;

    /**
     * show 页面会执行
     * @var callable $showInfoDo
     */
    protected $showInfoDo;

    /**
     * 数据保存前
     * @var callable $saveBeforeDo
     */
    protected $saveBeforeDo;

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




    ##################################################################################################################

    public function setIndexRowDo(callable $func): self
    {
        $this->indexRowDo=$func;
        return $this;
    }
    /**
     * @param VueCurlModel|BaseModel|BaseChildModel $row
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doIndexRowDo(VueCurlModel $row,?BaseModel $base,ModelField $field): self
    {
        $func=$this->indexRowDo?? static function(){};
        $func($row,$base,$field);
        return $this;
    }
    /**
     * 列表获取到后执行 方便函数
     * @param FieldCollection $field
     * @param Collection $list
     * @param BaseModel|null $base
     */
    public static function doIndex(FieldCollection $field,Collection $list,?BaseModel $base):void{

        $list->each(static function(VueCurlModel $row)use($field,$base){
            $field->each(static function(ModelField $field)use($base,$row){
                foreach ($field->getFieldDoList() as $fieldDo){
                    $fieldDo->doIndexRowDo($row,$base,$field);
                }
            });
        });
        //另外再遍历，为了方便前面那个遍历后得到的东西在下面使用
        $list->each(static function(VueCurlModel $row)use($field,$base,$list){
            $field->each(static function(ModelField $field)use($base,$row,$list){
                foreach ($field->getFieldDoList() as $fieldDo){
                    $fieldDo->doIndexListDo($list,$row,$base,$field);
                }
            });
        });
    }


    ##################################################################################################################


    public function setIndexListDo(callable $func): self
    {
        $this->indexListDo=$func;
        return $this;
    }
    public function doIndexListDo(Collection $list,VueCurlModel $row,?BaseModel $base,ModelField $field): self
    {
        $func=$this->indexListDo?? static function(){};
        $func($list,$row,$base,$field);
        return $this;
    }


    ##################################################################################################################


    public function setIndexFilterBeforeDo(callable $func): self
    {
        $this->indexListDo=$func;
        return $this;
    }
    public function doIndexFilterBeforeDo(ModelField $field,Query $query,array $filterData): self
    {
        $func=$this->indexFilterBeforeDo?? static function(){};
        $func($field,$query,$filterData);
        return $this;
    }
    public static function doIndexFilterBefore(FieldCollection $field,Query $query,array $filterData):void{
        $field->each(static function(ModelField $field)use($query,$filterData){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doIndexFilterBeforeDo($field,$query,$filterData);
            }
        });
    }



    ##################################################################################################################

    public function setShowInfoDo(callable $func): self
    {
        $this->showInfoDo=$func;
        return $this;
    }
    /**
     * @param VueCurlModel|BaseModel|BaseChildModel $info
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doShowInfoDo(VueCurlModel $info,?BaseModel $base,ModelField $field): self
    {
        $func=$this->showInfoDo?? static function(){};
        $func($info,$base,$field);
        return $this;
    }

    /**
     * 详情页字段钩子 方便函数
     * @param FieldCollection $field
     * @param VueCurlModel $info
     * @param BaseModel|null $base
     */
    public static function doShow(FieldCollection $field,VueCurlModel $info,?BaseModel $base):void{
        $field->each(static function(ModelField $field)use($base,$info){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doShowInfoDo($info,$base,$field);
            }
        });
    }

    ##################################################################################################################
    public function setSaveBeforeDo(callable $func): self
    {
        $this->saveBeforeDo=$func;
        return $this;
    }

    /**
     * 数据保存前字段钩子
     * @param array $postData   提交上来的数据
     * @param VueCurlModel|BaseModel|BaseChildModel|null $before
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doSaveBeforeDo(array &$postData,?VueCurlModel $before,?BaseModel $base,ModelField $field): self
    {
        $func=$this->saveBeforeDo?? static function(){};
        $func($postData,$before,$base,$field);
        return $this;
    }

    /**
     * 数据保存前字段钩子 方便函数
     * @param FieldCollection $field
     * @param array $postData   提交上来的数据
     * @param VueCurlModel|BaseModel|BaseChildModel|null $info
     * @param BaseModel|null $base
     */
    public static function doSaveBefore(FieldCollection $field,array &$postData,?VueCurlModel $info,?BaseModel $base):void{
        $field->each(static function(ModelField $field)use($base,$info,&$postData){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doSaveBeforeDo($postData,$info,$base,$field);
            }
        });
    }

    ##################################################################################################################


    public function setSaveAfterDo(callable $func): self
    {
        $this->saveAfterDo=$func;
        return $this;
    }

    /**
     * 数据保存后字段钩子
     * @param array $saveData   保存的数据
     * @param VueCurlModel|BaseModel|BaseChildModel|null $before
     * @param VueCurlModel|BaseModel|BaseChildModel $after
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doSaveAfterDo(array $saveData,?VueCurlModel $before,VueCurlModel $after,?BaseModel $base,ModelField $field): self
    {
        $func=$this->saveAfterDo?? static function(){};
        $func($saveData,$before,$after,$base,$field);
        return $this;
    }

    /**
     * 数据保存后字段钩子 方便函数
     * @param FieldCollection $field
     * @param array $saveData   保存的数据
     * @param VueCurlModel|BaseModel|BaseChildModel|null $before
     * @param VueCurlModel|BaseModel|BaseChildModel $after
     * @param BaseModel|null $base
     */
    public static function doSaveAfter(FieldCollection $field,array $saveData,?VueCurlModel $before,VueCurlModel $after,?BaseModel $base):void{
        $field->each(static function(ModelField $field)use($base,$before,$after,$saveData){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doSaveAfterDo($saveData,$before,$after,$base,$field);
            }
        });
    }

    ##################################################################################################################
    public function setEditShowDo(callable $func): self
    {
        $this->editShowDo=$func;
        return $this;
    }

    /**
     * 数据编辑显示字段钩子
     * @param VueCurlModel|BaseModel|BaseChildModel|null $info
     * @param BaseModel|null $base
     * @param ModelField $field
     * @return $this
     */
    public function doEditShowDo(?VueCurlModel $info,?BaseModel $base,ModelField $field): self
    {
        $func=$this->editShowDo?? static function(){};
        $func($info,$base,$field);
        return $this;
    }

    /**
     * 数据编辑显示字段钩子 方便函数
     * @param FieldCollection $field
     * @param VueCurlModel|BaseModel|BaseChildModel|null $info
     * @param BaseModel|null $base
     */
    public static function doEditShow(FieldCollection $field,?VueCurlModel $info,?BaseModel $base):void{
        $field->each(static function(ModelField $field)use($base,$info){
            foreach ($field->getFieldDoList() as $fieldDo){
                $fieldDo->doEditShowDo($info,$base,$field);
            }
        });
    }


    ##################################################################################################################

}