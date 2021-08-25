<?php


if (!function_exists('tpScriptVueCurdGetLoginData')) {
    /**
     * 返回当前登录人员信息
     * @return array
     */
    function tpScriptVueCurdGetLoginData():array{
        return [
            'id'=>1,//当前登录人ID
        ];
    }

}


if (!function_exists('tpScriptVueCurdGetLoginUrl')) {
    /**
     * 返回登录页面链接
     * @return string
     */
    function tpScriptVueCurdGetLoginUrl():string{
        return '/admin/login/index';
    }
}



if (!function_exists('tpScriptVueCurdGetHtmlTitle')) {
    /**
     * 网站名称
     * @return string
     */
    function tpScriptVueCurdGetHtmlTitle():string{
        return '';
    }
}


########################################################################################################################
#####################################################字段配置默认值############################################################

if (!function_exists('tpScriptVueCurdUploadDefaultUrl')) {
    /**
     * 文件上传默认url
     * url需返回 [code=1,msg=>'',data=>[id=1,original_name=>'',url=>'']]
     * @return string
     */
    function tpScriptVueCurdUploadDefaultUrl():string{
        return '/admin/ajax/upload';
    }
}

if (!function_exists('tpScriptVueCurdGetFileInfosByUrls')) {
    /**
     * 文件上传默认url
     * @param array $urls
     * @return array 返回的数据需为 [ [id=>1,url=>'',original_name=>''],[id=>2,url=>'',original_name=>''] ]
     */
    function tpScriptVueCurdGetFileInfosByUrls(array $urls):array{
        return [];
    }
}

if (!function_exists('tpScriptVueCurdPublicActionJsPathBase')) {
    /**
     * 自定义vue页面时可能使用
     * @return string
     */
    function tpScriptVueCurdPublicActionJsPathBase():string{
        return '/static/'.app('http')->getName().'/js/';
    }

}

if (!function_exists('tpScriptVueCurdImageRemoveMissings')) {
    /**
     * 自定义vue页面时可能使用
     * @return string
     */
    function tpScriptVueCurdImageRemoveMissings():bool{
        return false;
    }

}


########################################################################################################################
#####################################################模型字段############################################################

if (!function_exists('getModelDefaultCreateLoginUserField')) {
    /**
     * 当前表记录新增人的字段，为空字符串，表示不记录（所有表初始默认）
     * @return string
     */
    function getModelDefaultCreateLoginUserField():string{
        return 'create_admin_id';
    }
}

if (!function_exists('getModelDefaultUpdateLoginUserField')) {
    /**
     * 记录最新更改人的字段，为空字符串，表示不记录（所有表初始默认）
     * @return string
     */
    function getModelDefaultUpdateLoginUserField():string{
        return 'update_admin_id';
    }
}


if (!function_exists('getModelDefaultDeleteLoginUserField')) {
    /**
     * 记录删除人的字段，为空字符串，表示不记录（所有表初始默认）
     * @return string
     */
    function getModelDefaultDeleteLoginUserField():string{
        return 'delete_admin_id';
    }
}



if (!function_exists('getModelDefaultRegionField')) {
    /**
     * 所属地区ID字段，为空字符串，表示不记录（所有表初始默认）
     * @return string
     */
    function getModelDefaultRegionField():string{
        return '';
    }
}

if (!function_exists('getModelDefaultRegionPidField')) {
    /**
     * 所属地区父ID字段，为空字符串，表示不记录（所有表初始默认）
     * @return string
     */
    function getModelDefaultRegionPidField():string{
        return '';
    }
}


if (!function_exists('getModelDefaultStepField')) {
    /**
     * 字段步骤默认名称
     * @return string
     */
    function getModelDefaultStepField():string{
        return 'step';
    }
}

if (!function_exists('getModelDefaultNextStepField')) {
    /**
     * 字段下一个步骤默认名称
     * @return string
     */
    function getModelDefaultNextStepField():string{
        return 'next_step';
    }
}

if (!function_exists('imgFieldShowUrlDo')) {
    /**
     * 字段下一个步骤默认名称
     * @param string $urls
     * @param \tpScriptVueCurd\field\ImagesField $field
     * @return string
     */
    function imgFieldShowUrlDo(string $urls,\tpScriptVueCurd\field\ImagesField $field):string{
        return $urls;
    }
}

if (!function_exists('fileFieldShowUrlDo')) {
    /**
     * 字段下一个步骤默认名称
     * @param string $urls
     * @param \tpScriptVueCurd\field\FilesField $field
     * @return string
     */
    function fileFieldShowUrlDo(string $urls,\tpScriptVueCurd\field\FilesField $field):string{
        return $urls;
    }
}
########################################################################################################################
