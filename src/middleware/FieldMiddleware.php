<?php


namespace tpScriptVueCurd\middleware;


use tpScriptVueCurd\field\FilesField;

class FieldMiddleware
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}