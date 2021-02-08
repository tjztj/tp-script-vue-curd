<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\filter\LikeFilter;
use tpScriptVueCurd\ModelField;


/**
 * 密码输入
 * Class PasswordField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class PasswordField extends ModelField
{
    protected string $defaultFilterClass=EmptyFilter::class;
    protected bool $listShow=false;


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
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
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
        $excelFieldTpl->width=22;
    }

    /**
     * 显示时要处理的数据
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        $name=$this->name();
        if(isset($dataBaseData[$name])){
            $dataBaseData[$name]='••••••';
        }
    }
}