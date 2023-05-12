<?php


namespace tpScriptVueCurd\tool;


class ErrorCode
{

    public const DELETE_HAVE_CHILD=842001;//删除数据时，有子数据

    //请先删除下级数据
    public const DELETE_TREE_HAVE_CHILD=842002;//树形

    public const SAVE_IS_REQUIRED=842003;//保存时不能为空的错误值


    public const DEBUG_NOT_OUT_ERR_INFOS=[
        self::DELETE_HAVE_CHILD,
        self::DELETE_TREE_HAVE_CHILD,
        self::SAVE_IS_REQUIRED,
    ];
}