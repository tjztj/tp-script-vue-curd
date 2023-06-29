<?php

namespace tpScriptVueCurd\services;

use think\facade\Route;
use tpScriptVueCurd\traits\Func;

class ThinkPHPService extends \think\Service
{
    use Func;
    public function register()
    {
        $this->registerRoutes(function () {
            Route::any('tp-script-vue-curd/:c/:a', 'tpScriptVueCurd\actions\:c@:a')->pattern(['c' => '\w+', 'a' => '\w+']);
            Route::get('tpscriptvuecurd', function () {
                $this->readfile();
                exit;
            });
        });
    }


    private function readfile(): void
    {
        /*
   该PHP代码用于处理Web应用静态资源请求。
   它获取URL中的查询字符串并使用它来定位所请求的资源。
   然后检查所请求的资源是否在特定目录中有效，如果是，则将其提供给客户端。
*/

// 从URL中获取目录
        $dirs = array_values(array_filter(explode('/', str_replace('\\', '/', request()->server('REQUEST_URI') ?? '')), 'trim'));
        unset($dirs[0]);
        $dirs = array_values($dirs);


// 获取目录数组中的最后一个目录
        $lastDir = end($dirs);

// 查找最后一个目录中第一个问号和和符号的索引位置
        $endIndex = strcspn($lastDir, '?&');

// 如果最后一个目录中有问号或符号，则将其删除，并更新目录数组。
        if ($endIndex !== strlen($lastDir)) {
            $dirs[count($dirs) - 1] = substr($lastDir, 0, $endIndex);
        }

// 定义静态资源所在的基本路径
        $basePath = realpath(dirname(__DIR__) . '/../tp-script-vue-curd-static');

// 构建所请求资源的完整路径
        $path = realpath($basePath . '/' . implode('/', $dirs));

// 确保所请求的资源是基本路径中的有效文件，如果是，则提供它。
        if ($basePath === $path || strpos($path, $basePath . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
            // 如果基本路径中的所请求资源无效，则输出404错误并退出。
            http_response_code(404);
            exit();
        }

// 根据所请求资源的文件扩展名设置content type
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'css') {
            header('Content-type:text/css');
        } elseif ($ext === 'js') {
            header('Content-type:text/javascript');
        }

// 根据所请求资源的修改时间设置Last-Modified头
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');

// 将所请求资源的内容输出到客户端
        readfile($path);
    }
}