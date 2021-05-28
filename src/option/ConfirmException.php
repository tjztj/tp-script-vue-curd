<?php


namespace tpScriptVueCurd\option;


class ConfirmException extends \think\Exception
{

    public string $okText='确认执行';
    public string $cancelText='取消';
    public string $title='操作确认';


    private function __construct(string $message,string $okText='确认执行',string $cancelText='取消',string $title='操作确认',?int $errorCode=null)
    {
        if(is_null($errorCode)){
            $trace=$this->getTrace();
            $errorCode=count($trace).'0'.(current($trace)['line']).'0'.$this->getLine();
        }

        parent::__construct($message,$errorCode);

        $this->okText=$okText;
        $this->cancelText=$cancelText;
        $this->title=$title;
    }

    /**
     * 前台弹出确认款
     * @throws ConfirmException
     */
    public static function throw(string $message, string $okText='确认执行', string $cancelText='取消', string $title='操作确认', ?int $errorCode=null): void
    {
        $err=new self($message,$okText,$cancelText,$title,$errorCode);
        if(((int)request()->header('confirm-error-code'))===((int)$err->getCode())){
            return ;
        }
        throw $err;
    }

}