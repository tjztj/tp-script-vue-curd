<?php


namespace tpScriptVueCurd\option;


class FunControllerIndexPage
{
    /**
     * 每页显示数量，为0，不分页
     * @var int
     */
    public int $pageSize=10;


    /**
     * 如果为true，当获 get/post 取到 pageSize 参数时，使用获取到的 pageSize
     * @var bool
     */
    public bool $canGetRequestOption=true;

}