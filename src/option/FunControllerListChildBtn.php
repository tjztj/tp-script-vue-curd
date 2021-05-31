<?php


namespace tpScriptVueCurd\option;


class FunControllerListChildBtn
{

    /**
     * 按钮颜色
     * @var string
     */
    public string $color='';


    /**
     * 是否显示
     * @var bool
     */
    public bool $show=true;


    /**
     * 按钮文字
     * @var string
     */
    public string $text='';

    /**
     * 按钮连接，为null，自动生成
     * @var string|null
     */
    public ?string $url=null;

}