<?php


namespace tpScriptVueCurd\option;


use Throwable;

class ConfirmException extends \think\Exception
{

    public string $okText='确认执行';
    public string $cancelText='取消';


    public function __construct(string $message,int $errorCode,string $okText='确认执行',string $cancelText='取消')
    {
        parent::__construct($message,$errorCode);
        $this->okText=$okText;
        $this->cancelText=$cancelText;
    }

}