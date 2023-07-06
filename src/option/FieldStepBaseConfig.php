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
    public string $listBtnTextEdit='';//编辑时显示文字
    public string $titleEdit='';//编辑时显示文字
    public ?string $listBtnColorEdit=null;//编辑时的标题
    public array $other=[];
    public bool $okRefreshList=false;//操作成功后，是否刷新整个列表而不单单刷新当前数据
    private ?bool $canEditReturn=null;//FieldStepBase中canEdit的返回值，null代表未执行到canEdit
    public array $canEditActions=[];//可以提交此步骤字段信息的action集合


    /**
     * 步骤与数据关联后将会执行 = function(FieldStepBaseConfig $config,BaseModel $old,BaseModel $parentInfo=null,FieldStep $step,bool $isNext){}
     * @var \Closure|null
     */
    public ?\Closure $onInfo=null;

    public function __construct(array $config=[]){
        $arr=$this->toArray();
        foreach ($config as $k=>$v){
            if(isset($arr[$k])){
                $this->$k=$v;
            }
        }
        if(isset($config['onInfo'])){
            $this->onInfo=$config['onInfo'];
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
            'title'=>$this->title,
            'listBtnTextEdit'=>$this->listBtnTextEdit,
            'titleEdit'=>$this->titleEdit,
            'listBtnColorEdit'=>$this->listBtnColorEdit,
            'other'=>$this->other,
            'okRefreshList'=>$this->okRefreshList,
            'canEditActions'=>$this->canEditActions,
        ];
    }

}