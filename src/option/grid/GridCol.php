<?php

namespace tpScriptVueCurd\option\grid;


class GridCol
{
    /**
     * https://juejin.cn/post/6854573220306255880#heading-13
     * https://codepen.io/gpingfeng/pen/PoZgopr
     */
    public ?string $gridColumnStart=null;
    public ?string $gridColumnEnd=null;
    public ?string $gridRowStart=null;
    public ?string $gridRowEnd=null;

    public function __construct($gridColumnStart=null,$gridColumnEnd=null,$gridRowStart=null,$gridRowEnd=null)
    {
        $this->gridColumnStart=$gridColumnStart;
        $this->gridColumnEnd=$gridColumnEnd;
        $this->gridRowStart=$gridRowStart;
        $this->gridRowEnd=$gridRowEnd;
    }


    public function toArray(){
        return [
            'gridColumnStart'=>$this->gridColumnStart,
            'gridColumnEnd'=>$this->gridColumnEnd,
            'gridRowStart'=>$this->gridRowStart,
            'gridRowEnd'=>$this->gridRowEnd,
        ];
    }

}