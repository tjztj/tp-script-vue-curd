<?php

namespace tpScriptVueCurd\actions;

class UeditorDo
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