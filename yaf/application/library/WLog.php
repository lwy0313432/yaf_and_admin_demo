<?php
/**
 * @describe: SeasLog 的简单包裹,兼容 SeasLog
 * @author: Jerry Yang(hy0kle@gmail.com)
 * */
class WLog
{
    const trace_on  = 1;
    const trace_off = 0;
    /**
     * SeasLog::func_name($message, array $content = array(), $sub_module = '', $is_trace = 0);
     *
     * $message: 日志内容
     * $content: 处理 $message 中占位符
     * $sub_module: 子模块名,用于问题追踪,默认为 -
     * $is_trace: 是否打开堆栈回朔,默认不追踪
     *
     * example: WLog::notice(__FILE__, array(), 'events', 1);
     * */
    public static function __callStatic($func_name, $arguments)
    {
        $argc = count($arguments);
        $args = array();
        $args[0] = isset($arguments[0])? $arguments[0] : '';
        $message = ' [ERR_MSG] '.$args[0];
        $args[1] = array();
        if ($argc > 1) {
            $message .= " | [PARAM] ".json_encode($arguments[1]);
        } 
        $arrConfig = Yaf_Application::app()->getConfig();
        $args[2] = $arrConfig['app']['name'] ? $arrConfig['app']['name'] :'default';
        $backtrace = debug_backtrace();
        $trace = $backtrace[1];
        $trace2 = $backtrace[2];
        $file = $trace['file'];
        $line = $trace['line'];
        $function = isset($trace2['function']) ? $trace2['function'] : '';
        $class = isset($trace2['class']) ? $trace2['class'] : '';
        $message .= '| [BACKTRACE] file: '.$file.' +'.$line. ',class:'.$class.',function:'.$function;
        $message .=' | [REQUEST_URI] '.$_SERVER['REQUEST_URI'];
        $message .=' | [GET_PARAM] '.json_encode($_GET);
        $message .=' | [POST_PARAM] '.json_encode($_POST);
        $args[0] = $message;

        call_user_func_array('SeasLog::' . $func_name, $args);
    }
}
/* vim:set ts=4 sw=4 et fdm=marker: */
