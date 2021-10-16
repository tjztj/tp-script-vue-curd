<?php


namespace tpScriptVueCurd\traits\model;


trait ModelBaseField
{
    /**
     * 关联字段（默认为base_id）,子类重写此方法改变，如果有，代表有父表
     * @return string
     */
    public static function parentField():string{
        return 'base_id';
    }

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

    
}