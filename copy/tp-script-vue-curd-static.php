<?php
$basePath = dirname(__DIR__) . '/vendor/tj/tp-script-vue-curd/tp-script-vue-curd-static';
$dirs = array_filter(explode('/', str_replace('\\', '/', $_SERVER['QUERY_STRING'] ?? '')), 'trim');
$lastDir = end($dirs);
$endIndex1 = strpos($lastDir, '?');
$endIndex2 = strpos($lastDir, '&');
if ($endIndex1 !== false && $endIndex2 !== false) {
    $endIndex = min($endIndex1, $endIndex2);
} else {
    $endIndex = $endIndex1 === false ? $endIndex2 : $endIndex1;
}
if ($endIndex !== false) {
    $dirs[count($dirs) - 1] = substr($lastDir, 0, $endIndex);
}
$path = realpath($basePath . '/' . implode('/', $dirs));
if ($basePath === $path || strpos($path, $basePath . '/') !== 0 || !file_exists($path)) {
    http_response_code(404);
    exit();
}
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
if ($ext === 'css') {
    header('Content-type:text/css');
} elseif ($ext === 'js') {
    header('Content-type:text/javascript');
}
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
readfile($path);