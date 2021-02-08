<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\LikeFilter;
use tpScriptVueCurd\ModelField;


/**
 * 短字符串
 * Class StringField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class StringField extends ModelField
{
    protected string $defaultFilterClass=LikeFilter::class;


    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        if(isset($data[$this->name()])){
            $this->save=trim($data[$this->name()]);
            if($this->required()&&$this->save===''){
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
}