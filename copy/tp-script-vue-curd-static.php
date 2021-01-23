<?php

const VUE_CURD_STATIC_PATH='./tp-script-vue-curd-static';

if(empty($_SERVER['QUERY_STRING'])){
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    echo '未获取到文件名';
    exit;
}


$dirs=array_filter(explode('/',$_SERVER['QUERY_STRING']));
$lastDir=end($dirs);
$endIndex=strpos($lastDir,'?');
$endIndex===false||$dirs[count($dirs)-1]=substr($lastDir,0,$endIndex);

$path=VUE_CURD_STATIC_PATH.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR,$dirs);
if(!is_file($path)){
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    echo '未找到:'.$path;
    exit;
}


$ext=strtolower(pathinfo($path, PATHINFO_EXTENSION));
if($ext==='css'){
    header('Content-type:text/css');
}else if($ext==='js'){
    header('Content-type:text/javascript');
}
echo file_get_contents($path);