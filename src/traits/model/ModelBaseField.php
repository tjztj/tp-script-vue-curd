<?php


namespace tpScriptVueCurd\traits\model;


trait ModelBaseField
{
    /**
     * 当前表记录新增人的字段，为空字符串，表示不记录
     * @return string
     */
    public static function getCreateLoginUserField():string{
        return getModelDefaultCreateLoginUserField();
    }

    /**
     * 记录最新更改人的字段，为空字符串，表示不记录
     * @return string
     */
    public static function getUpdateLoginUserField():string{
        return getModelDefaultUpdateLoginUserField();
    }

    /**
     * 记录删除人的字段，为空字符串，表示不记录
     * @return string
     */
    public static function getDeleteLoginUserField():string{
        return getModelDefaultDeleteLoginUserField();
    }



    /**
     * 所属地区ID字段，为空字符串，表示不记录
     * @return string
     */
    public static function getRegionField():string{
        return getModelDefaultRegionField();
    }


    /**
     * 所属地区父ID字段，为空字符串，表示不记录
     * @return string
     */
    public static function getRegionPidField():string{
        return getModelDefaultRegionPidField();
    }
    
}