<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\LikeFilter;
use tpScriptVueCurd\ModelField;


/**
 * 多个短字符串
 * Class MoreStringField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class MoreStringField extends ModelField
{
    protected string $defaultFilterClass=LikeFilter::class;

    protected string $separate='|';



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
    public function setSave(array $data): self
    {
        if(isset($data[$this->name()])){
            $this->save=is_array($data[$this->name()])?implode($this->separate(),$data[$this->name()]):$data[$this->name()];
            $this->defaultCheckRequired($this->save);
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
        $excelFieldTpl->explain='多个请用‘'.$this->separate().'’隔开';
    }
}