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
 * 长字符串
 * Class TextField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class StringAutoCompleteField extends ModelField
{
    protected string $defaultFilterClass=LikeFilter::class;

    protected bool $beginGetOptions=false;////在开始时，如果值为空，先获取所有选项

    /**
     * 要搜索的url,如果不需要url也能搜索的话，建议使用select
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

    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        if(isset($data[$this->name()])){
            $this->save=trim($data[$this->name()]);
            if($this->required()&&($this->save===''||$this->save===$this->nullVal())){
                $this->defaultCheckRequired($this->save);
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
        $excelFieldTpl->width=22;
    }


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tp-script-vue-curd-static.php?field/string_auto_complete/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeVarchar();
    }
}