<?php

namespace tpScriptVueCurd\actions;

class Ueditor
{
    public function index(){
        $action=request()->param('action');
        switch ($action){
            case 'uploadimage':
            case 'uploadfile':
            case 'uploadscrawl':
            case 'uploadvideo':
                return uEditorUpload();
        }
    }
}