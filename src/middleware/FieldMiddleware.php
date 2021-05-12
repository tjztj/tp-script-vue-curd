<?php


namespace tpScriptVueCurd\middleware;


class FieldMiddleware
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }
}