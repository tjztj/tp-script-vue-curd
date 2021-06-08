<?php


namespace tpScriptVueCurd\tool;


use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class Excel
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\tool
 */
class Excel
{


    /** 导出excel文件信息
     * @param $config
     * @param array $expCellName
     * @param array $expTableData
     * @param string $title
     * @param null $th
     * @param int $type 导出的类型，默认为.xlsx,否者为.xls
     * @param null $ftitle 副标题
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    static function exportExecl($config, $expCellName = [], $expTableData = [], $title = '', $th = null, $type = 0, $ftitle = null)
    {
        if (isset($config['expTitle']) && isset($config['expCellName']) && isset($config['expTableData']) && isset($config['title'])) {
            $expTitle = $config['expTitle'];
            $expCellName = $config['expCellName'];
            $expTableData = $config['expTableData'];
            $title = $config['title'];
            if (isset($config['th'])) $th = $config['th'];
            if (isset($config['type'])) $type = $config['type'];
            if (isset($config['ftitle'])) $ftitle = $config['ftitle'];
        } else {
            $expTitle = $config;
        }


        $objPHPExcel = new Spreadsheet();
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $xlsTitle ?: Time::unixtimeToDate('YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        foreach ($expCellName as $k => $v) {
            if (isset($v[0])) $expCellName[$k]['name'] = $v[0];
            if (isset($v[1])) {
                $expCellName[$k]['title'] = $v[1];
            } else if (isset($v[0])) {
                $expCellName[$k]['title'] = $v[0];
            }
            if (isset($v[2])) $expCellName[$k]['rowspan'] = $v[2];
            if (isset($v[3])) $expCellName[$k]['colspan'] = $v[3];
            if (isset($v[4])) $expCellName[$k]['style'] = $v[4];


            isset($expCellName[$k]['name']) || $expCellName[$k]['name'] = '';
            isset($expCellName[$k]['title']) || $expCellName[$k]['title'] = '';
            isset($expCellName[$k]['rowspan']) || $expCellName[$k]['rowspan'] = '';
            isset($expCellName[$k]['colspan']) || $expCellName[$k]['colspan'] = '';
            isset($expCellName[$k]['style']) || $expCellName[$k]['style'] = '';
//        isset($expCellName[$k]['width'])||$expCellName[$k]['width']='';
//        isset($expCellName[$k]['wrap_text'])||$expCellName[$k]['wrap_text']=true;

        }
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        $cellStart='A';
        $cellName=[$cellStart];
        for($i=0;$i<=$cellNum;$i++){
            $cellStart++;
            $cellName[]=$cellStart;
        }

        $styleArray1 = [
            'font' => [
//            'bold' => true,
                'size' => 20,
                'name' => 'Simhei',
                'color' => ['argb' => '00000000']
            ],
            'alignment' => [
                'horizontal' => empty($config['title_horizontal']) ? Alignment::HORIZONTAL_CENTER : $config['title_horizontal'],
                'vertical' => empty($config['title_vertical']) ? Alignment::VERTICAL_CENTER : $config['title_vertical'],
            ],
        ];
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Microsoft YaHei UI Light');
        // 将A1单元格设置为加粗，居中
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray1);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(50);
//    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
//    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setName('Simhei');
        $objPHPExcel->getActiveSheet()->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d H:i:s'));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $title);

        $begin_th = $th ? count($th) + 1 : 2;
        if ($ftitle != null) {
            $styleArray2 = [
                'font' => [
                    'color' => ['argb' => '00000000']
                ]
            ];
            $objPHPExcel->getActiveSheet()
                ->getStyle('A2')
                ->applyFromArray($styleArray2);
            $objPHPExcel->getActiveSheet()->mergeCells('A2:' . $cellName[$cellNum - 1] . '2'); // 合并单元格
            // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.' Export time:'.date('Y-m-d H:i:s'));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', $ftitle);
            $begin_th++;
        }
        if ($th) {
            foreach ($th as $key => $val) {
                foreach ($val as $v) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($v[0] . $key, $v[3]);
                    $objPHPExcel->getActiveSheet()->getStyle($v[0] . $key)->applyFromArray(
                        ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER], 'font' => ['bold' => true]]
                    );
                    if (!empty($v[0]) || !empty($v[1])) {
                        $x = $key;
                        $y = $v[0];
                        empty($v[2]) || $x += $v[2] - 1;
                        empty($v[1]) || $y = $cellName[array_search($v[0], $cellName) + $v[1] - 1];
                        $objPHPExcel->getActiveSheet()->mergeCells($v[0] . $key . ':' . $y . $x);
                    }
                }
            }
        } else {
            for ($i = 0; $i < $cellNum; $i++) {
                if (isset($expCellName[$i]['width'])) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($expCellName[$i]['width']);
                }
                if (isset($expCellName[$i]['wrap_text']) && $expCellName[$i]['wrap_text'] == true) {
                    $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setWrapText(true);
                }
                if (isset($expCellName[$i]['is_text']) && $expCellName[$i]['is_text'] == true) {
                    $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                }
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . $begin_th, $expCellName[$i]['title']);
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . $begin_th)->applyFromArray(['font' => ['bold' => true]]);
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setVertical(Alignment::VERTICAL_TOP);//文字靠顶部
            }
        }

        $begin_th++;
        $objPHPExcel->getActiveSheet()->freezePane("A" . (empty($config['freezePane']) ? $begin_th : $config['freezePane'])); // 表头冻结（滚动不动）
        for ($i = 0; $i < $dataNum; $i++) {
            $n = 0;
            for ($j = 0; $j < $cellNum; $j++) {
                $data_key = $expCellName[$j]['name'];
                if (stripos($data_key, ':') === 0) {
                    $vo = $expTableData[$i];
                    $data_key = substr($data_key, 1);
                    try {
                        $data_value = eval('return ' . $data_key . ';');
                    } catch (\Exception $e) {
                        $data_value = '';
                    }
                } else {
                    $data_value = empty($data_key) ? '' : $expTableData[$i][$data_key];
                }
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j + $n] . ($i + $begin_th), $data_value);
                if (!empty($expCellName[$j]['rowspan']) && $th) {
                    $n += $expCellName[$j]['rowspan'] - 1;
                    $objPHPExcel->getActiveSheet()->mergeCells($cellName[$j] . ($i + $begin_th) . ':' . $cellName[$j + $n] . ($i + $begin_th));
                }
            }
        }
        ob_end_clean();
        header('pragma:public');
        if (empty($type)) {
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
            header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
            $objWriter = IOFactory::createWriter($objPHPExcel, "Xlsx");
        } else {
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
            header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
            $objWriter = IOFactory::createWriter($objPHPExcel, 'xls');
        }
        $objWriter->save('php://output');
        exit;
    }



    /**
     * 使用PHPEXECL导入
     *
     * @param string $file      文件地址
     * @param int    $sheet     工作表sheet(传0则获取第一个sheet)
     * @param int    $columnCnt 列数(传0则自动获取最大列)
     * @param array  $options   操作选项
     *                          array mergeCells 合并单元格数组
     *                          array formula    公式数组
     *                          array format     单元格格式数组
     *
     * @return array
     * @throws \think\Exception
     */
    static function importExecl(string $file = '', int $sheet = 0, int $columnCnt = 0, &$options = [])
    {

        if(!is_file($file)){
            /* 转码 */
            $file = iconv("utf-8", "gb2312", $file);

            if (empty($file) OR !file_exists($file)) {
                throw new \think\Exception('文件不存在!');
            }
        }


        /** @var Xlsx $objRead */
        $objRead = IOFactory::createReader('Xlsx');


        if (!$objRead->canRead($file)) {
            /** @var Xls $objRead */
            $objRead = IOFactory::createReader('Xls');

            if (!$objRead->canRead($file)) {
                throw new \think\Exception('只支持导入Excel文件！');
            }
        }

        /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
        empty($options) && $objRead->setReadDataOnly(true);
        /* 建立excel对象 */
        $obj = $objRead->load($file);
        /* 获取指定的sheet表 */
        $currSheet = $obj->getSheet($sheet);

        $data=[];
        if(!empty($options['img_save_path'])){
            foreach ($currSheet->getDrawingCollection()->getIterator() as $drawing){
                $source=null;
                list($startColumn, $startRow) = Coordinate::coordinateFromString($drawing->getCoordinates());
                $imageFileName = $drawing->getCoordinates().'_'. random_int(1000, 9999).'_'.time().'_'.$drawing->getHashCode();
                if($drawing instanceof MemoryDrawing){
                    $imageFileName .= '.png';
                    $source = $drawing->getImageResource();
                    //把图片存起来
                    imagepng($source, $options['img_save_path']. $imageFileName);
                }else{
                    switch ($drawing->getExtension()) {
                        case 'jpg':
                        case 'jpeg':
                            $imageFileName .= '.jpg';
                            $source = @imagecreatefromjpeg($drawing->getPath());
                            break;
                        case 'gif':
                            $imageFileName .= '.gif';
                            $source = @imagecreatefromgif($drawing->getPath());
                            break;
                        case 'png':
                            $imageFileName .= '.png';
                            $source = @imagecreatefrompng($drawing->getPath());
                            break;
                    }
                    $source || $source=imagecreatefromstring(file_get_contents($drawing->getPath()));
                    imagepng($source, $options['img_save_path']. $imageFileName);
                }

                if(!empty($source)){
                    if(imagesx ($source)<180||imagesy ($source)<180){
                        throw new \think\Exception('在单元格'.$startColumn.$startRow.'中，有图片小于标准(180*180)，不能导入。或联系客服');
                    }
                    //一个单元格内可能多张图片
                    isset($data[$startRow])||$data[$startRow]=[];
                    isset($data[$startRow][$startColumn])||$data[$startRow][$startColumn]=[];
                    $cell=$currSheet->getCell($drawing->getCoordinates());
                    if($cell->isInMergeRange()){
                        $cell_arr=explode(':',$cell->getMergeRange());
                        list(,$startRow) = Coordinate::coordinateFromString($cell_arr[0]);
                        list(,$endRow) = Coordinate::coordinateFromString($cell_arr[1]);
                        $val=$options['img_save_path']. $imageFileName;
                        for($i=$startRow;$i<=$endRow;$i++){
                            isset($data[$i])||$data[$i]=[];
                            isset($data[$i][$startColumn])||$data[$i][$startColumn]=[];
                            $data[$i][$startColumn][]=$val;
                        }
                    }else{
                        $data[$startRow][$startColumn][]=$options['img_save_path']. $imageFileName;
                    }
                }
            }
        }




        if (isset($options['mergeCells'])) {
            /* 读取合并行列 */
            $options['mergeCells'] = $currSheet->getMergeCells();
        }

        if (0 == $columnCnt) {
            /* 取得最大的列号 */
            $columnH = $currSheet->getHighestColumn();
            /* 兼容原逻辑，循环时使用的是小于等于 */
            $columnCnt = Coordinate::columnIndexFromString($columnH);
        }

        /* 获取总行数 */
        $rowCnt = $currSheet->getHighestRow();

        /* 读取内容 */
        for ($_row = 1; $_row <= $rowCnt; $_row++) {
            $isNull = true;

            for ($_column = 1; $_column <= $columnCnt; $_column++) {
                $cellName = Coordinate::stringFromColumnIndex($_column);
                $cellId   = $cellName . $_row;
                $cell     = $currSheet->getCell($cellId);



                if (isset($options['format'])) {
                    /* 获取格式 */
                    $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                    /* 记录格式 */
                    $options['format'][$_row][$cellName] = $format;
                }

                if (isset($options['formula'])) {
                    /* 获取公式，公式均为=号开头数据 */
                    $formula = $currSheet->getCell($cellId)->getValue();

                    if (0 === strpos($formula, '=')) {
                        $options['formula'][$cellName . $_row] = $formula;
                    }
                }

                if (isset($format) && 'm/d/yyyy' == $format) {
                    /* 日期格式翻转处理 */
                    $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                }



                //可能有图片上传了
                if(!isset($data[$_row][$cellName])){
                    try{
                        $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());
                    }catch (\Exception $e){
                        try{
                            $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());
                        }catch (\Exception $e){
                            $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getValue());
                        }
                    }
                }


                if (!empty($data[$_row][$cellName])) {
                    if(is_string($data[$_row][$cellName])){
                        $val=$currSheet->getCell($cellId)->getCalculatedValue();
                        if((preg_match('/^\-\"(\d+\.?\d*)\"$/u',$data[$_row][$cellName])>0&&is_numeric($val))
                            ||(is_numeric($val)&&$data[$_row][$cellName]===('"'.$val.'"'))){
                            $data[$_row][$cellName]=$data[$_row][$cellName]= $val;
                        }
                    }
                    $isNull = false;
                }

                if($cell->isInMergeRange()){
                    $cell_arr=explode(':',$cell->getMergeRange());
                    list(,$startRow) = Coordinate::coordinateFromString($cell_arr[0]);
                    list(,$endRow) = Coordinate::coordinateFromString($cell_arr[1]);
                    if($startRow<$endRow){
                        for($i=$startRow;$i<=$endRow;$i++){
                            isset($data[$i])||$data[$i]=[];
                            $data[$i][$cellName]=$data[$_row][$cellName];
                        }
                    }
                }

                if($isNull){
                    $isNull=isset($data[$_row][$cellName])&&$data[$_row][$cellName]!=='';
                }
            }

            /* 判断是否整行数据为空，是的话删除该行数据 */
            if ($isNull) {
                unset($data[$_row]);
                //如果整行没数据，就不再执行了
                break;
            }
        }

        return $data;
    }



    /**
     * 格式化内容
     *
     * @param array $array 头部规则
     * @return false|mixed|null|string 内容值
     */
    protected static function formatting(array $array, $value, $row)
    {
        !isset($array[2]) && $array[2] = 'text';

        switch ($array[2])
        {
            // 文本
            case 'text' :
                return $value;
            // 日期
            case  'date' :
                return !empty($value) ? Time::unixtimeToDate($array[3], $value) : null;
            // 月分
            case  'month' :
                return !empty($value) ? Time::unixtimeToDate($array[3].'/01', $value) : null;
            // 选择框
            case  'selectd' :
                return  $array[3][$value] ?? null ;
            // 匿名函数
            case  'function' :
                return isset($array[3]) ? call_user_func($array[3], $row) : null;
            // 默认
            default :

                break;
        }

        return null;
    }

    /**
     * 解析字段
     *
     * @param $row
     * @param $field
     * @return mixed
     */
    protected static function formattingField($row, $field)
    {
        $newField = explode('.', $field);
        if (count($newField) == 1) {
            return $row[$field];
        }

        foreach ($newField as $item) {
            if (isset($row[$item])) {
                $row = $row[$item];
            } else {
                break;
            }
        }

        return is_array($row) ? false : $row;
    }
}