<?php

namespace tpScriptVueCurd\services;

use think\facade\App;
use think\facade\Route;
use think\facade\Request;
use think\facade\Lang;
use think\facade\Db;
class ThinkPHPService extends \think\Service
{
    public function register()
    {
        $this->registerRoutes(function (){
            Route::any('tp-script-vue-curd/:c/:a','tpScriptVueCurd\actions\:c@:a');
        });
    }

}