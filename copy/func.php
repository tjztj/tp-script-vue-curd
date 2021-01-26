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
