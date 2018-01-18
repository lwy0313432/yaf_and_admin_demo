<?php
/**
 * @describe: 自定义异常类
 * @author: Jerry Yang(hy0kle@gmail.com)
 * */
class CException extends Exception
{
    protected $code = -1;
    protected $message = '';
    public function __construct($code)
    {
        $this->code = $code;
        $this->message =Errno::getMessage($code);
    }
}
/* vi:set ts=4 sw=4 et fdm=marker: */
