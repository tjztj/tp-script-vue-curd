<?php

namespace tpScriptVueCurd\services;

use think\facade\Route;
class ThinkPHPService extends \think\Service
{
    public function register()
    {
        $this->registerRoutes(function (){
            Route::any('tp-script-vue-curd/:c/:a','tpScriptVueCurd\actions\:c@:a');
        });
    }

}