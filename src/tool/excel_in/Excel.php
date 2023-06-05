<?php


namespace tpScriptVueCurd\tool\excel_in;


use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use tpScriptVueCurd\tool\Time;

/**
 * Class Excel
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd\tool
 */
class Excel
{
    /**
     * 使用PHPEXECL导入
     *
     * @param string $file 文件地址
     * @param int $sheet 工作表sheet(传0则获取第一个sheet)
     * @param int $columnCnt 列数(传0则自动获取最大列)
     * @param array $options 操作选项
     *                          array mergeCells 合并单元格数组
     *                          array formula    公式数组
     *                          array format     单元格格式数组
     *
     * @return array
     * @throws \think\Exception
     */
    static function importExecl(string $file = '', int $sheet = 0, int $columnCnt = 0, &$options = [])
    {

        if (!is_file($file)) {
            /* 转码 */
            $file = iconv("utf-8", "gb2312", $file);

            if (empty($file) or !file_exists($file)) {
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

        $data = [];
        if (!empty($options['img_save_path'])) {
            $drawingList=$currSheet->getDrawingCollection()->getIterator();
            if($drawingList&&count($drawingList)>0&&!is_dir($options['img_save_path'])){
                mkdir($options['img_save_path'],0777,true);
            }
            foreach ($drawingList as $drawing) {
                $source = null;
                list($startColumn, $startRow) = Coordinate::coordinateFromString($drawing->getCoordinates());
                $imageFileName = $drawing->getCoordinates() . '_' . random_int(1000, 9999) . '_' . time() . '_' . $drawing->getHashCode();
                if ($drawing instanceof MemoryDrawing) {
                    $imageFileName .= '.png';
                    $source = $drawing->getImageResource();
                    //把图片存起来
                    imagepng($source, $options['img_save_path'] . $imageFileName);
                } else {
                    switch (strtolower($drawing->getExtension())) {
                        case 'jpg':
                        case 'jpeg':
                            $imageFileName .= '.jpg';
                            $source = @imagecreatefromjpeg($drawing->getPath());
                            $source || $source = imagecreatefromstring(file_get_contents($drawing->getPath()));
                            imagejpeg($source, $options['img_save_path'] . $imageFileName);
                            break;
                        case 'gif':
                            $imageFileName .= '.gif';
                            $source = @imagecreatefromgif($drawing->getPath());
                            $source || $source = imagecreatefromstring(file_get_contents($drawing->getPath()));
                            imagegif($source, $options['img_save_path'] . $imageFileName);
                            break;
                        case 'png':
                            $imageFileName .= '.png';
                            $source = @imagecreatefrompng($drawing->getPath());
                            $source || $source = imagecreatefromstring(file_get_contents($drawing->getPath()));
                            imagepng($source, $options['img_save_path'] . $imageFileName);
                            break;
                    }

                }

                if (!empty($source)) {
                    if (imagesx($source) < 180 || imagesy($source) < 180) {
                        throw new \think\Exception('在单元格' . $startColumn . $startRow . '中，有图片小于标准(180*180)，不能导入。或联系客服');
                    }
                    //一个单元格内可能多张图片
                    isset($data[$startRow]) || $data[$startRow] = [];
                    isset($data[$startRow][$startColumn]) || $data[$startRow][$startColumn] = [];
                    $cell = $currSheet->getCell($drawing->getCoordinates());
                    if ($cell->isInMergeRange()) {
                        $cell_arr = explode(':', $cell->getMergeRange());
                        list(, $startRow) = Coordinate::coordinateFromString($cell_arr[0]);
                        list(, $endRow) = Coordinate::coordinateFromString($cell_arr[1]);
                        $val =str_replace(['\\','//'],'/', $options['img_save_path'] . $imageFileName);
                        for ($i = $startRow; $i <= $endRow; $i++) {
                            isset($data[$i]) || $data[$i] = [];
                            isset($data[$i][$startColumn]) || $data[$i][$startColumn] = [];
                            $data[$i][$startColumn][] = $val;
                        }
                    } else {
                        $data[$startRow][$startColumn][] = str_replace(['\\','//'],'/', $options['img_save_path'] . $imageFileName);
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
                $cellId = $cellName . $_row;
                $cell = $currSheet->getCell($cellId);


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

                if (isset($format) && 'm/d/yyyy' === $format) {
                    /* 日期格式翻转处理 */
                    $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                }


                //可能有图片上传了
                if (!isset($data[$_row][$cellName])) {
                    try {
                        $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());
                    } catch (\Exception $e) {
                        try {
                            $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());
                        } catch (\Exception $e) {
                            $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getValue());
                        }
                    }
                }


                if (!empty($data[$_row][$cellName])) {
                    if (is_string($data[$_row][$cellName])) {
                        $val = $currSheet->getCell($cellId)->getCalculatedValue();
                        if ((preg_match('/^\-\"(\d+\.?\d*)\"$/u', $data[$_row][$cellName]) > 0 && is_numeric($val))
                            || (is_numeric($val) && $data[$_row][$cellName] === ('"' . $val . '"'))) {
                            $data[$_row][$cellName] = $val;
                        }
                    }
                    $isNull = false;
                }

                if ($cell->isInMergeRange()) {
                    $cell_arr = explode(':', $cell->getMergeRange());
                    list(, $startRow) = Coordinate::coordinateFromString($cell_arr[0]);
                    list(, $endRow) = Coordinate::coordinateFromString($cell_arr[1]);
                    if ($startRow < $endRow) {
                        for ($i = $startRow; $i <= $endRow; $i++) {
                            isset($data[$i]) || $data[$i] = [];
                            $data[$i][$cellName] = $data[$_row][$cellName];
                        }
                    }
                }

                if ($isNull) {
                    $isNull = isset($data[$_row][$cellName]) && $data[$_row][$cellName] !== '';
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

        switch ($array[2]) {
            // 文本
            case 'text' :
                return $value;
            // 日期
            case  'date' :
                return !empty($value) ? Time::unixtimeToDate($array[3], $value) : null;
            // 月分
            case  'month' :
                return !empty($value) ? Time::unixtimeToDate($array[3] . '/01', $value) : null;
            // 选择框
            case  'selectd' :
                return $array[3][$value] ?? null;
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