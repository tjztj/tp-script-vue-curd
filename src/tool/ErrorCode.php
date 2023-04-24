<?php


namespace tpScriptVueCurd\tool;


class ErrorCode
{

    public const DELETE_HAVE_CHILD=842001;//删除数据时，有子数据

    //请先删除下级数据
    public const DELETE_TREE_HAVE_CHILD=842002;//树形
}