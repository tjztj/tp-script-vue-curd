<?php

namespace tpScriptVueCurdCopy;

use Composer\Script\Event;
class install
{

    public static function copyAboutFiles(Event $event):void{
        var_dump(dirname(__DIR__) . DIRECTORY_SEPARATOR) ;

    }
}