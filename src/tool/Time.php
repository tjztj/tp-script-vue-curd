<?php


namespace tpScriptVueCurd\tool;

/**
 * Class Time
 * @package tpScriptVueCurd\tool
 */
class Time
{
    public static function unixtimeToDate($format,$unixtime=null, $timezone = 'PRC'): string
    {
        if(is_null($unixtime)){
            $unixtime=time();
        }
        $datetime = new \DateTime("@$unixtime"); //DateTime类的bug，加入@可以将Unix时间戳作为参数传入
        $datetime->setTimezone(new \DateTimeZone($timezone));
        return $datetime->format($format);
    }
    public static function dateToUnixtime($date, $timezone = 'PRC') {
        if(empty($date)){
            throw new \think\Exception('传入时间参数为空了');
        }
        return (new \DateTime($date, new \DateTimeZone($timezone)))->format('U');
    }
}