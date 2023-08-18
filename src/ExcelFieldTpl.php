<?php


namespace tpScriptVueCurd;

/**
 * Class ExcelFieldTpl
 * @package tpScriptVueCurd
 * @author tj 1079798840@qq.com
 */
class ExcelFieldTpl
{

    public string $name='';//字段名称
    public string $title='';//字段标题
    public string $group='';//分组
    public int $width=0;//0代表不设定
    public string $explain='填写任意字符';//字段导出备注信息
    public bool $wrapText=false;//单元格内是否可换行
    public bool $isText=false;//该列是否文本格式
    public array $items=[];//下拉选项
    public string $type='';//字段类型
    public ?ModelField $field=null;



    public function __construct(string $name,string $title)
    {
        $this->name=$name;
        $this->title=$title;
    }
}