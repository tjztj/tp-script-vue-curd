<?php

const VUE_CURD_STATIC_PATH='..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'tj'.DIRECTORY_SEPARATOR.'tp-script-vue-curd'.DIRECTORY_SEPARATOR.'tp-script-vue-curd-static';

if(empty($_SERVER['QUERY_STRING'])){
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    echo '未获取到文件名';
    exit;
}


$dirs=array_map(function($v){
    return trim($v,'&');
},array_filter(explode('/',$_SERVER['QUERY_STRING'])));
$lastDir=end($dirs);
$endIndex1=strpos($lastDir,'?');
$endIndex2=strpos($lastDir,'&');
if($endIndex1===false&&$endIndex2!==false){
    $endIndex=$endIndex2;
}else if($endIndex1!==false&&$endIndex2===false){
    $endIndex=$endIndex1;
}else if($endIndex1!==false&&$endIndex2!==false){
    $endIndex=$endIndex1>$endIndex2?$endIndex2:$endIndex1;
}else{
    $endIndex=false;
}
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