<?php


/**
 * static 防止重复访问数据库 获取登录人员信息
 * @return []
 */
function staticTpScriptVueCurdGetLoginData():array{
    static $data;
    if(!$data){
        $data=tpScriptVueCurdGetLoginData();
    }
    return $data;
}