<?php


namespace tpScriptVueCurd\tool\excel_out;

/**
 *示例
$heads=[
    ['name'=>'type','value'=>'类别',],
    ['name'=>'project_name','value'=>'项目名称',],
    ['name'=>'inspection_num_sum','value'=>'检查次数',],
    ['name'=>'count_val','value'=>'发现问题数',],
    ['name'=>'pp_count','value'=>'处置总人数',],
    ['value'=>'第一种形态处置人数','childs'=>[
        ['value'=>'不含诫勉谈话','format'=>fn($v)=>'bbb'],
            ['value'=>'子列表2','childs'=>[
                ['value'=>'子列表2--A','format'=>fn($v)=>$v['id']],
                ['name'=>'admonish_val_count','value'=>'子列表2--B',],
            ]],
        ]
    ],
    ['name'=>'discipline_count','value'=>'党纪政务处分人数',],
    ['name'=>'proposal_yes_count','value'=>'发放监察建议书',],
];
ExportExcel::make('“五项监督”子库一览表')->setThead($heads)->setData($list)->out();
 */


use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportExcel
{

    public Spreadsheet $excel;
    public string $fontName='Microsoft YaHei UI Light';//字体类型
    public ExportCell $title;
    public string $fileName='';//保存名称

    public bool $freezePane=true;//冻结表头

    /**
     * @var ExportCell[]
     */
    private array $ths=[];

    private int $thMaxRow=1;//将包含标题

    private string $thMaxCol='A';

    private int $maxRow=1;

    private array $colThs=[];//表头

    private array $items=[];



    public function __construct(string $title)
    {
        $this->excel=new Spreadsheet();
        $this->title=new ExportCell($title);
        $this->title->row=$this->thMaxRow;
        $this->title->col='A';
        $this->title->fontSize=20;
        $this->title->fontName='Simhei';
        $this->title->fontColor='00000000';
        $this->title->alignmentHorizontal=Alignment::HORIZONTAL_CENTER;
        $this->title->alignmentVertical=Alignment::VERTICAL_CENTER;
    }


    public static function make(string $title){
        return new self($title);
    }



    /**
     * 数组转换为 单元格对象
     * @param array $arr
     * @param string $cellPath
     * @return ExportCell
     */
    public static function arrToCell(array $arr,string $cellPath=ExportCell::class):ExportCell{
        $row=$cellPath===ExportThCell::class?new $cellPath($arr['value'],$arr['name']??''):new $cellPath($arr['value']);
        foreach ($arr as $k=>$v){
            $row->$k=$v;
        }


        return $row;
    }




    /**
     * 表头设置
     * @param array|ExportThCell[] $ths        表头数组数据,ExportThCell[]是为了方便查看
     * @return $this
     */
    public function setThead(array $ths):self{
        $this->doTh($ths);

        //表头跨行处理
        foreach ($this->ths as $v){
            /**
             * @var ExportThCell $v
             */
            if($this->thMaxRow>($v->row+$v->haveLevel)){
                $v->mergeCellsRow=$this->thMaxRow-$v->row-$v->haveLevel;
            }
        }

        return $this;
    }


    /**
     * 处理表头设置
     * @param array $ths 表头数组数据
     * @param string $colNum 表头初始列
     * @param int $haveLevel
     * @param int $rowNum
     * @return array
     * @throws \think\Exception
     */
    private function doTh(array $ths,string $colNum='A',int $haveLevel=0,int $rowNum=0){
        if(empty($ths)){
            throw new \think\Exception('表头不能设置为空');
        }
        if(empty($rowNum)){
            $this->thMaxRow++;
            $rowNum=$this->thMaxRow;
            $this->maxRow=$rowNum;
        }else if($rowNum>$this->thMaxRow){
            $this->thMaxRow=$rowNum;
            $this->maxRow=$rowNum;
        }
        foreach ($ths as $v){
            /**
             * @var ExportThCell $cell
             */
            $cell=self::arrToCell($v,ExportThCell::class);
            $cell->col=$colNum;
            $cell->row=$rowNum;

            //设置默认属性
            isset($cell->fontColor)|| $cell->fontColor='00000000';
            isset($cell->fontBold)|| $cell->fontBold=true;

            $this->colThs[$colNum]=$cell;

            $this->ths[]=$cell;
            if($cell->childs){
                [$colNum,$newHaveLevel]=$this->doTh($cell->childs,$colNum,$haveLevel+1,$rowNum+1);
                $cell->mergeCellsCol=ExportOperation::operationSubGetNum($colNum,$cell->col);
                $cell->haveLevel=$newHaveLevel-$haveLevel;
                $haveLevel=$newHaveLevel;
            }else{
                $cell->haveLevel=0;
            }
            if(ExportOperation::operationComp($colNum,$this->thMaxCol)>0){
                $this->thMaxCol=$colNum;
            }
            $colNum++;
        }
        return [ExportOperation::operationSub($colNum,1),$haveLevel];
    }


    /**
     * 设置数据
     * @param array $data
     * @return $this
     */
    public function setData(array $data):self{
        foreach ($data as $v){
            $items=[];
            foreach ($this->colThs as $col=>$th){
                /**
                 * @var ExportThCell $th
                 */
                if(empty($th->name)){
                    if(empty($th->format)){
                        continue;
                    }
                    $func=$th->format;
                    $cell=self::arrToCell([
                        'value'=>'',
                        'col'=>$col,
                        'th'=>$th,
                    ]);
                    $cell->value=$func($v,$cell);
                }else{
                    if(!isset($v[$th->name])){
                        continue;
                    }
                    $val=$v[$th->name];
                    if($val instanceof ExportCell){
                        $cell=$val;
                    }else{
                        $cell=self::arrToCell(['value'=>$val]);
                    }
                    $cell->col=$col;
                    $cell->th=$th;
                }

                $items[]=$cell;
            }
            if($items){
                $this->maxRow++;
                foreach ($items as $item){
                    $item->row=$this->maxRow;
                    if(isset($item->th->format)){
                        $func=$item->th->format;
                        $item->value=$func($v,$item);
                    }
                    $this->items[]=$item;
                }
            }
        }

        return $this;
    }



    public function out(string $outPath='php://output'):void{
        //标题跨行
        if(ExportOperation::operationComp($this->thMaxCol,$this->title->col)>0){
            $this->title->mergeCellsCol=ExportOperation::operationSubGetNum($this->thMaxCol,$this->title->col);
        }


        //标题 和 表头 加入到前面
        array_unshift($this->items,$this->title,...$this->ths);

        // 表头冻结（滚动不动）
        $this->excel->getActiveSheet()->freezePane("A" . ($this->thMaxRow+1));


        //设置默认字体
        if($this->fontName){
            $this->excel->getDefaultStyle()->getFont()->setName($this->fontName);
        }


        $maxH=[];
        $maxW=[];
        foreach ($this->items as $v){
            /**
             * @var ExportCell $v
             */
            $style=$this->excel->getActiveSheet()->getStyle($v->col.$v->row);
            isset($v->fontSize)&&$style->getFont()->setSize($v->fontSize);
            isset($v->fontName)&&$style->getFont()->setName($v->fontName);
            isset($v->fontColor)&&$style->getFont()->setColor(new Color($v->fontColor));
            isset($v->fontBold)&&$style->getFont()->setBold($v->fontBold);
            isset($v->alignmentVertical)&&$style->getAlignment()->setVertical($v->alignmentVertical);
            isset($v->alignmentHorizontal)&&$style->getAlignment()->setHorizontal($v->alignmentHorizontal);

            if(isset($v->height)&&(!isset($maxH[$v->row])||$v->height>$maxH[$v->row])){
                $maxH[$v->row]=$v->height;
            }
            if(isset($v->width)&&(!isset($maxW[$v->col])||$v->width>$maxW[$v->col])){
                $maxW[$v->col]=$v->width;
            }

            isset($v->wrapText)&&$style->getAlignment()->setWrapText($v->wrapText);

            if(!empty($v->mergeCellsCol)||!empty($v->mergeCellsRow)){
                $mergeCellsCol=empty($v->mergeCellsCol)?0:$v->mergeCellsCol;
                $mergeCellsRow=empty($v->mergeCellsRow)?0:$v->mergeCellsRow;
                $this->excel->getActiveSheet()->mergeCells($v->col . $v->row . ':' . (ExportOperation::operationAdd($v->col,$mergeCellsCol)) . bcadd($v->row,$mergeCellsRow));
            }
            if(isset($v->formatText)&&$v->formatText===true){
                $style->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                $this->excel->getActiveSheet()->setCellValueExplicit($v->col . $v->row,$v->value,DataType::TYPE_STRING);
            }else{
                $this->excel->getActiveSheet()->setCellValue($v->col . $v->row,$v->value);
            }

            if(isset($v->do)){
                //处理excel对函数
                ($v->do)($this->excel,$v);
            }
        }

        foreach ($maxH as $row=>$v){
            $this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight($v);
        }
        foreach ($maxW as $col=>$v){
            $this->excel->getActiveSheet()->getColumnDimension($col)->setWidth($v);
        }

        $fileName=iconv('utf-8', 'gb2312',$this->fileName?:$this->title->value.date('Y_m_d___H_i_s'));

        $outPath=strtolower($outPath);
        if($outPath==='php://output'){
            ob_end_clean();
            header('pragma:public');
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $fileName . '.xlsx"');
            header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印

        }

        $objWriter = IOFactory::createWriter($this->excel, "Xlsx");
        $objWriter->save($outPath);

        if($outPath==='php://output'){
            exit;
        }
    }
}