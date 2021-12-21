<?php


namespace tpScriptVueCurd\tool\excel_out;


class ExportCell
{

    public int $row;//第几行
    public string $col;//第几列
    public int $mergeCellsRow;//合并单元格行数
    public int $mergeCellsCol;//合并单元格列数

    public int $fontSize;//字体大小
    public string $fontName;//字体类型
    public string $fontColor;//字体颜色
    public bool $fontBold;//是否加粗
    public string $alignmentHorizontal;//水平对齐
    public string $alignmentVertical;//垂直对齐
    public int $height;//单元格高度
    public int $width;//单元格宽度

    public bool $wrapText;//是否可换行
    public bool $formatText;//是否设置为文本格式

    public $do;//对当前excel对处理函数

    public $value;//设置值

    public $th;//对应表头


    public function __construct($value)
    {
        $this->value=$value;
    }

}