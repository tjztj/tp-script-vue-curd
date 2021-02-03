<?php


/**
 * 返回当前登录人员信息
 * @return array
 */
function getLoginData():array{
    return [
        'id'=>1,//当前登录人ID
    ];
}

/**
 * 返回登录页面链接
 * @return string
 */
function getLoginUrl():string{
    return '/admin/login/index';
}

/**
 * 网站名称
 * @return string
 */
function getHtmlTitle():string{
    return '';
}


########################################################################################################################
#####################################################模型字段############################################################

/**
 * 当前表记录新增人的字段，为空字符串，表示不记录（所有表初始默认）
 * @return string
 */
function getModelDefaultCreateLoginUserField():string{
    return 'create_admin_id';
}

/**
 * 记录最新更改人的字段，为空字符串，表示不记录（所有表初始默认）
 * @return string
 */
function getModelDefaultUpdateLoginUserField():string{
    return 'update_admin_id';
}

/**
 * 记录删除人的字段，为空字符串，表示不记录（所有表初始默认）
 * @return string
 */
function getModelDefaultDeleteLoginUserField():string{
    return 'delete_admin_id';
}



/**
 * 所属地区ID字段，为空字符串，表示不记录（所有表初始默认）
 * @return string
 */
function getModelDefaultRegionField():string{
    return '';
}


/**
 * 所属地区父ID字段，为空字符串，表示不记录（所有表初始默认）
 * @return string
 */
function getModelDefaultRegionPidField():string{
    return '';
}

########################################################################################################################
