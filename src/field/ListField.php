<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
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

    protected bool $canExcelImport=false;//不能使用excel导入数据
    protected bool $canExport=false;//不能导出此字段数据
    /**
     * @var FieldCollection |ModelField[]
     */
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
        $this->fieldPushAttrByWhere('fields',$fields);
        return $this;
    }

    /**
     * 显示时对数据处理
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        parent::doShowData($dataBaseData);

        $this->fields=$this->fields->filter(fn(ModelField $v)=>$v->showPage())->rendGroup();
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
    public function setSaveVal(array $data,BaseModel $old): self
    {
        if(isset($data[$this->name()])){
            $list=[];
            if(!is_array($data[$this->name()])){
                $arr=[];
                if($data[$this->name()]!==''){
                    //如果是正确的json
                    $arr=json_decode($data[$this->name()],true);
                    if(empty($arr)){
                        $arr=json_decode(htmlspecialchars_decode($data[$this->name()]),true);
                        empty($arr)||$data[$this->name()]=htmlspecialchars_decode($data[$this->name()]);
                    }
                };
                $list=$arr?:[];
            }else{
                $list=$data[$this->name()];
            }
            foreach ($list as $k=>$v){
                $fieldsObj=clone $this->fields;
                $fieldsObj->setSave($v,$old);
                $list[$k]=$fieldsObj->getSave();
                foreach ($fieldsObj as $val){
                    $val->save=null;
                }
            }
            if(empty($list)){
                $this->save=$this->nullVal();
                $this->defaultCheckRequired($this->nullVal());
            }else{
                $this->save=json_encode($list);
            }
        }else{
            $this->save=$this->nullVal();
            $this->defaultCheckRequired($this->nullVal());
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
            new Index($type,'/tpscriptvuecurd/field/list/index.js'),
            new Show($type,'/tpscriptvuecurd/field/list/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/list/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeJson();
    }

    public function getOtherComponentJsFields(): ?FieldCollection
    {
        return $this->fields();
    }
}