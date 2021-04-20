<?php


namespace tpScriptVueCurd\option;


use think\Collection;
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
     * show 页面会执行
     * @var callable $showInfoDo
     */
    protected $showInfoDo;



    public function setIndexRowDo(callable $func): self
    {
        $this->indexRowDo=$func;
        return $this;
    }
    public function setIndexListDo(callable $func): self
    {
        $this->indexListDo=$func;
        return $this;
    }
    public function setShowInfoDo(callable $func): self
    {
        $this->showInfoDo=$func;
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
    public function doIndexListDo(Collection $list,VueCurlModel $row,?BaseModel $base,ModelField $field): self
    {
        $func=$this->indexListDo?? static function(){};
        $func($list,$row,$base,$field);
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
     * 列表获取到后执行 方便函数
     * @param FieldCollection $field
     * @param Collection $list
     * @param BaseModel|null $base
     */
    public static function doIndex(FieldCollection $field,Collection $list,?BaseModel $base):void{

        $list->each(static function(VueCurlModel $row)use($field,$base){
            $field->each(static function(ModelField $field)use($base,$row){
                $fieldDo=$field->getFieldDo();
                if(!$fieldDo){
                    return;
                }
                $fieldDo->doIndexRowDo($row,$base,$field);
            });
        });
        //另外再遍历，为了方便前面那个遍历后得到的东西在下面使用
        $list->each(static function(VueCurlModel $row)use($field,$base,$list){
            $field->each(static function(ModelField $field)use($base,$row,$list){
                $fieldDo=$field->getFieldDo();
                if(!$fieldDo){
                    return;
                }
                $fieldDo->doIndexListDo($list,$row,$base,$field);
            });
        });
    }


    /**
     * 详情页字段钩子 方便函数
     * @param FieldCollection $field
     * @param VueCurlModel $info
     * @param BaseModel|null $base
     */
    public static function doShow(FieldCollection $field,VueCurlModel $info,?BaseModel $base):void{
        $field->each(static function(ModelField $field)use($base,$info){
            $fieldDo=$field->getFieldDo();
            if(!$fieldDo){
                return;
            }
            $fieldDo->doShowInfoDo($info,$base,$field);
        });
    }

}