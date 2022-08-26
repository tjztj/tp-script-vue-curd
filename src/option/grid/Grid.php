<?php
namespace tpScriptVueCurd\option\grid;

/**
 * 页面布局
 * css grid 学习 https://juejin.cn/post/6854573220306255880
 */
class Grid
{
    /**
     * 每列宽度以及列分布情况
     * 使用文档https://blog.csdn.net/u013565133/article/details/102912734
     * @var string|null
     */
    public ?string $gridTemplateColumns=null;

    /*
     * 每行宽度以及列分布情况
     * 使用文档https://blog.csdn.net/u013565133/article/details/102912734
     */
    public ?string $gridTemplateRows=null;


    /**
     * 单元格之间的空格
     * @var string|null
     */
    public ?string $gap=null;


    /**
     * https://juejin.cn/post/6854573220306255880#heading-8
     * @var string|null
     */
    public ?string $gridAutoFlow=null;


    /**
     * #https://juejin.cn/post/6854573220306255880#heading-7
     * @var string|null
     */
    public ?string $gridTemplateAreas=null;


    /**
     * https://juejin.cn/post/6854573220306255880#heading-9
     * @var string|null
     */
    public ?string $justifyItems=null;
    public ?string $alignItems=null;
    public ?string $placeItems=null;



    public function toArray(){
        return [
            'gridTemplateColumns'=>$this->gridTemplateColumns,
            'gridTemplateRows'=>$this->gridTemplateRows,
            'gap'=>$this->gap,
            'gridAutoFlow'=>$this->gridAutoFlow,
            'gridTemplateAreas'=>$this->gridTemplateAreas,
            'justifyItems'=>$this->justifyItems,
            'alignItems'=>$this->alignItems,
            'placeItems'=>$this->placeItems,
        ];
    }
}