<?php


namespace tpScriptVueCurd\tool\excel_out;


class ExportThCell extends ExportCell
{
    public string $name;

    /**
     * @var self[]
     */
    public array $childs=[];

    /**
     * $format($row,$cell)
     * @var callable $format
     */
    public $format;//格式化


    public int $haveLevel=0;//下面包含多少个子集

    public function __construct($value,string $name='')
    {
        parent::__construct($value);
        $this->name=$name;
    }
}