<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\LikeFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;


/**
 * 多个短字符串
 * Class MoreStringField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class MoreStringField extends ModelField
{
    protected string $defaultFilterClass=LikeFilter::class;

    protected bool $beginGetOptions=false;//在开始时，如果值为空，先获取所有选项

    protected string $separate='|';


    /**
     * 要搜索的url,如果不需要url也能搜索的话，建议使用select（如果有，与 StringAutoCompleteField 一致）
     * @var string
     */
    protected string $url='';


    /**要搜索的url
     * @param string|null $url
     * @return $this|int
     */
    public function url(string $url = null)
    {
        return $this->doAttr('url', $url);
    }

    /**在开始时，如果值为空，先获取所有选项
     * @param bool|null $beginGetOptions
     * @return $this|bool
     */
    public function beginGetOptions(bool $beginGetOptions = null)
    {
        return $this->doAttr('beginGetOptions', $beginGetOptions);
    }


    /**分隔符
     * @param string|null $separate
     * @return $this|string
     */
    public function separate(string $separate=null){
        return $this->doAttr('separate',$separate);
    }

    /**
     * 显示时对数据处理
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        parent::doShowData($dataBaseData);
        if(isset($dataBaseData[$this->name()])){
            $dataBaseData[$this->name().'Arr']=explode($this->separate,$dataBaseData[$this->name()]);
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
            $this->save=is_array($data[$this->name()])?implode($this->separate(),$data[$this->name()]):$data[$this->name()];
            $this->defaultCheckRequired($this->save);
        }else{
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
        $excelFieldTpl->width=22;
        $excelFieldTpl->explain='多个请用‘'.$this->separate().'’隔开';
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,'/tp-script-vue-curd-static.php?field/more_string/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/more_string/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeVarchar(400);
    }
}