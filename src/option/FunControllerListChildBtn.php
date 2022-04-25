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


    /**
     * 打开页面显示位置  auto|rb|lt|lb
     * @var string
     */
    public string $pageOffset='rt';

    /**
     * 是否可添加子表数据
     * @var bool
     */
    public ?bool $canAdd=null;

}