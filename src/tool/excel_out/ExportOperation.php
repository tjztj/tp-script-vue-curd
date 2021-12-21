<?php


namespace tpScriptVueCurd\tool\excel_out;


use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportOperation
{
    /**
     * 字母运算 加
     * @param string|int $param1
     * @param string|int $param2
     * @return string
     */
    public static function operationAdd($param1,$param2):string{
        is_numeric($param1)||$param1=Coordinate::columnIndexFromString($param1);
        is_numeric($param2)||$param2=Coordinate::columnIndexFromString($param2);
        return Coordinate::stringFromColumnIndex(bcadd($param1,$param2));
    }

    /**
     * 字母运算 减
     * @param string $param1
     * @param string $param2
     * @return int
     */
    public static function operationSubGetNum(string $param1,string $param2):int{
        return bcsub(Coordinate::columnIndexFromString($param1),Coordinate::columnIndexFromString($param2));
    }

    /**
     * 字母运算 减
     * @param string|int $param1
     * @param string|int $param2
     * @return string
     */
    public static function operationSub($param1,$param2):string{
        is_numeric($param1)||$param1=Coordinate::columnIndexFromString($param1);
        is_numeric($param2)||$param2=Coordinate::columnIndexFromString($param2);
        return Coordinate::stringFromColumnIndex(bcsub($param1,$param2));
    }

    /**
     * 字母运算 比较
     * @param string|int $param1
     * @param string|int $param2
     * @return int
     */
    public static function operationComp($param1,$param2):int{
        is_numeric($param1)||$param1=Coordinate::columnIndexFromString($param1);
        is_numeric($param2)||$param2=Coordinate::columnIndexFromString($param2);
        return bccomp($param1,$param2);
    }
}