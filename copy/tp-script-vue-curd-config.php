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
     * 文件上传字段显示时，处理显示内容
     * @param array $urls
     * @return array 返回的数据需为 [ url=>[id=>1,url=>'',original_name=>''],[id=>2,url=>'',original_name=>''] ]
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



########################################################################################################################
#####################################################模型字段############################################################

if (!function_exists('getModelDefaultCreateLoginUserField')) {
    /**
     * 当前表记录新增人的字段，为空字符串，表示不记录（所有表初始默认）
     * @return string
     */
    function getModelDefaultCreateLoginUserField():string{
        return 'create_system_admin_id';
    }
}

if (!function_exists('getModelDefaultUpdateLoginUserField')) {
    /**
     * 记录最新更改人的字段，为空字符串，表示不记录（所有表初始默认）
     * @return string
     */
    function getModelDefaultUpdateLoginUserField():string{
        return 'update_system_admin_id';
    }
}


if (!function_exists('getModelDefaultDeleteLoginUserField')) {
    /**
     * 记录删除人的字段，为空字符串，表示不记录（所有表初始默认）
     * @return string
     */
    function getModelDefaultDeleteLoginUserField():string{
        return 'delete_system_admin_id';
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

if (!function_exists('getModelDefaultStepPastsField')) {
    /**
     * 数据当前流程已走步骤名称集合
     * @return string
     */
    function getModelDefaultStepPastsField():string{
        return 'step_pasts';
    }
}


if (!function_exists('getModelDefaultCurrentStepField')) {
    /**
     * 字段当前步骤默认名称
     * @return string
     */
    function getModelDefaultCurrentStepField():string{
        return 'current_step';
    }
}

if (!function_exists('imgFieldShowUrlDo')) {
    /**
     * 数据图片显示前
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
     * 数据文件显示前
     * @param string $urls
     * @param \tpScriptVueCurd\field\FilesField $field
     * @return string
     */
    function fileFieldShowUrlDo(string $urls,\tpScriptVueCurd\field\FilesField $field):string{
        return $urls;
    }
}
########################################################################################################################
if (!function_exists('tableThemIsColor')) {
    /**
     * 列表中的表头是否为渐变表头
     * @return bool
     */
    function tableThemIsColor():bool{
        return true;
    }
}
########################################################################################################################
if (!function_exists('tsvcThemCssPath')) {
    /**
     * 自定义主题文件路径（更改默认样式）
     * @return string
     */
    function tsvcThemCssPath():string{
        return '';
    }
}
########################################################################################################################
if (!function_exists('uEditorUpload')) {
    /**
     * 百度文本编辑器实现文件上传
     * @return string
     */
    function uEditorUpload():string{
        $file=request()->file('upfile');

        //上传失败返回示例
        return json_encode([
            "state" => '请在tp-script-vue-curd-config.php/uEditorUpload中实现上传',
        ], JSON_THROW_ON_ERROR);

        //上传成功返回示例
        return json_encode([
            "state" => 'SUCCESS',
            "url" => '',
            "title" => '',
            "original" => '',
            "type" => '',
            "size" => ''
        ], JSON_THROW_ON_ERROR);
    }
}
########################################################################################################################