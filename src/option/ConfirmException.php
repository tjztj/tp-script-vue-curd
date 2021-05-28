<?php


namespace tpScriptVueCurd\option;


class ConfirmException extends \think\Exception
{

    public string $okText='确认执行';
    public string $cancelText='取消';


    public function __construct(string $message,string $okText='确认执行',string $cancelText='取消',?int $errorCode=null)
    {
        if(is_null($errorCode)){
            $trace=$this->getTrace();
            $errorCode=count($trace).'0'.(current($trace)['line']).'0'.$this->getLine();
        }

        parent::__construct($message,$errorCode);

        $this->okText=$okText;
        $this->cancelText=$cancelText;
    }

}