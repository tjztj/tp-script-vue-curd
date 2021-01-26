<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\ModelField;


/**
 * 多个字段列表
 * Class ListField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class ListField extends ModelField
{
    protected string $defaultFilterClass=EmptyFilter::class;

    protected bool $listShow=false;
    protected bool $canExcelImport=false;//不能使用excel导入数据
    protected FieldCollection $fields;//字段集合


    public function readOnly(bool $readOnly = null)
    {
        if(!is_null($readOnly)){
            if(!isset($this->fields)){
                throw new \think\Exception('字段'.$this->name().'需要先设置fields');
            }
            $this->fields->each(function(ModelField $field)use($readOnly){
                $field->readOnly($readOnly);
            });
        }
        return parent::readOnly($readOnly);
    }


    /**
     * 是否在后台列表中显示出来
     * @param bool|null $listShow
     * @return $this|bool
     */
    public function listShow(bool $listShow=null){
        if($listShow===true){
            throw new \think\Exception('字段类型[ListField]不能设置listShow未true');
        }
        return $this->doAttr('listShow',$listShow);
    }


    /**
     * 字段集合
     * @param FieldCollection|null $fields
     * @return $this|FieldCollection
     */
    public function fields(FieldCollection $fields=null){
        if(is_null($fields)){
            if(!isset($this->fields)){
                throw new \think\Exception('未设置字段'.$this->name().'的fields');
            }
            return $this->fields;
        }
        $this->fields=$fields;
        return $this;
    }

    /**
     * 显示时对数据处理
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        parent::doShowData($dataBaseData);
        if(isset($dataBaseData[$this->name()])){
            if(is_null($this->fields)){
                throw new \think\Exception('未设置字段'.$this->name().'的fields');
            }
            if($dataBaseData[$this->name()]){
                $list=json_decode($dataBaseData[$this->name()],true);
                foreach ($list as &$v){
                    $this->fields->doShowData($v);
                }
            }else{
                $list=[];
            }
            $dataBaseData[$this->name().'List']=$list;
        }
    }

    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSave(array $data): self
    {
        if(isset($data[$this->name()])){
            $list=[];
            if(is_array($data[$this->name()])){
                foreach ($data[$this->name()] as $k=>$v){
                    $fieldsObj=clone $this->fields;
                    $fieldsObj->setSave($v);
                    $list[]=$fieldsObj->getSave();
                }
                $this->save=json_encode($list);
            }else{
                if($data[$this->name()]!==''){
                    //如果是正确的json
                    $list=json_decode($data[$this->name()],true);
                }
                $this->save=$data[$this->name()];
            }
            if(empty($list)){
                $this->defaultCheckRequired('');
            }
        }else{
            $this->defaultCheckRequired('');
        }
        return $this;
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->explain="此列不可导入数据";
    }
}