<?php


namespace tpScriptVueCurd\traits\model;


trait ModelBaseField
{
    /**
     * 当前表记录新增人的字段，为空字符串，表示不记录
     * @return string
     */
    public static function getCreateLoginUserField():string{
        return 'create_admin_id';
    }

    /**
     * 记录最新更改人的字段，为空字符串，表示不记录
     * @return string
     */
    public static function getUpdateLoginUserField():string{
        return 'update_admin_id';
    }

    /**
     * 记录删除人的字段，为空字符串，表示不记录
     * @return string
     */
    public static function getDeleteLoginUserField():string{
        return 'delete_admin_id';
    }

    /**
     * 所属地区ID字段，为空字符串，表示不记录
     * @return string
     */
    public static function getRegionField():string{
        if(static::getRegionPidField()===''){
            throw new \think\Exception('设置了getRegionField，需再设置getRegionPidField');
        }
        return '';
    }


    /**
     * 所属地区父ID字段，为空字符串，表示不记录
     * @return string
     */
    public static function getRegionPidField():string{
        if(static::getRegionField()===''){
            throw new \think\Exception('设置了getRegionPidField，需再设置getRegionField');
        }
        return '';
    }
}