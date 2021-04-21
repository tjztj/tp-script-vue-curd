<?php


namespace tpScriptVueCurd\option;


class FieldStepBaseConfig
{

    public ?string $color=null;
    public string $listBtnText='';//如果有值，列表中将显示,
    public string $listBtnUrl='';//地址
    public ?string $listBtnColor=null;
    public string $listBtnClass='';
    public int $listBtnWidth=0;
    public string $listBtnOpenWidth='45vw';
    public string $listBtnOpenHeight='100vh';
    public string $title='';


    public function __construct(array $config=[]){
        $arr=$this->toArray();
        foreach ($config as $k=>$v){
            if(isset($arr[$k])){
                $this->$k=$v;
            }
        }
    }


    public function toArray():array{
        return [
            'color'=>$this->color,
            'listBtnText'=>$this->listBtnText,
            'listBtnUrl'=>$this->listBtnUrl,
            'listBtnColor'=>$this->listBtnColor,
            'listBtnClass'=>$this->listBtnClass,
            'listBtnWidth'=>$this->listBtnWidth,
            'listBtnOpenWidth'=>$this->listBtnOpenWidth,
            'listBtnOpenHeight'=>$this->listBtnOpenHeight,
        ];
    }

}