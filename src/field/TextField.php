<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\LikeFilter;
use tpScriptVueCurd\ModelField;


/**
 * 长字符串
 * Class TextField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class TextField extends ModelField
{
    protected string $defaultFilterClass=LikeFilter::class;


    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSave(array $data): self
    {
        if(isset($data[$this->name()])){
            $this->save=trim($data[$this->name()]);
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
        $excelFieldTpl->isText=true;
        $excelFieldTpl->wrapText=true;
        $excelFieldTpl->width=40;
    }
}