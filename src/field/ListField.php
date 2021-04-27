<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;


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
    protected $nullVal='null';//字段在数据库中为空时的值


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
                if($list&&is_array($list)){
                    foreach ($list as &$v){
                        $this->fields->doShowData($v);
                    }
                }else{
                    $list=[];
                }
            }else{
                $list=[];
            }

            $dataBaseData[$this->name().'ShowComponentUrl']=$this->fields->getComponents('show');
            $dataBaseData[$this->name().'List']=$list;
        }
    }




    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
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
                    if(empty($list)){
                        $list=json_decode(htmlspecialchars_decode($data[$this->name()]),true);
                        empty($list)||$data[$this->name()]=htmlspecialchars_decode($data[$this->name()]);
                    }
                }
                $this->save=$data[$this->name()];
            }
            if(empty($list)){
                $this->save='null';
                $this->defaultCheckRequired('');
            }
        }else{
            $this->save='null';
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


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tp-script-vue-curd-static.php?field/list/index.js'),
            new Show($type,'/tp-script-vue-curd-static.php?field/list/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/list/edit.js')
        );
    }
}