<?php


namespace tpScriptVueCurd\field;
use think\Exception;
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
     * 要搜索的url,如果不需要url也能搜索的话，建议使用select，也可以对当前字段的items赋值
     * @var string
     */
    protected string $url='';

    /**
     * 默认就有的选项，一维数组['选项1','选项2','选项3']
     * @var array
     */
    protected array $items=[];



    /**
     * url返回的结果是否会被筛选
     * @var bool
     */
    protected bool $willFilter=true;

    /**
     * 要搜索的url,如果不需要url也能搜索的话，建议使用select，也可以对当前字段的items赋值
     * @param string|null $url
     * @return $this|int
     */
    public function url(string $url = null)
    {
        return $this->doAttr('url', $url);
    }


    /**
     * url返回的结果是否会被筛选
     * @param bool|null $willFilter
     * @return bool|StringAutoCompleteField
     */
    public function willFilter(bool $willFilter = null)
    {
        return $this->doAttr('willFilter', $willFilter);
    }


    /**
     * 默认就有的选项，一维数组['选项1','选项2','选项3']
     * @param array|null $items
     * @return $this|int
     */
    public function items(array $items = null)
    {
        return $this->doAttr('items', $items);
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
     * @param array $data 数据值集合
     * @param BaseModel $old
     * @return $this
     * @throws Exception
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
            new Edit($type,'/tpscriptvuecurd/field/string_auto_complete/edit.js')
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