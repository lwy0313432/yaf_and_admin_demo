<?php
/**
 * @describe: 自定义捕获错误处理机制
 * @author: Jerry Yang(hy0kle@gmail.com)
 * */
final class CErrorHandler
{
    public static function setCustomErrorHandler()
    {
        set_error_handler('CErrorHandler::customErrorHandler', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    }

    public static function customErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $log = sprintf('errno: %d | errmsg: %s | file: %s +%d | request_uri: %s',
            $errno, $errstr, $errfile, $errline,
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cli');
        //SeasLog::error($log, array(), 'catch-error');
        error_log($log);
    }
}
/* vim:set ts=4 sw=4 et fdm=marker: */

