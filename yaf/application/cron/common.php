<?php
/**
 * @package yindou
 * @brief  person_loan cron 头部
 * @author weixiaotong <weixt@yindou.com>
 * @date 2017-2-24
 * @encoding UTF-8
 * @copyright (c) yindou
 */

//初始化时区
define('APPLICATION_PATH', dirname(dirname(dirname(__FILE__))));
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
$response = $application->bootstrap();
//$application->bootstrap()->run();
//只能在命令行下运行
if (php_sapi_name() != "cli") {
    return (date('Y-m-d H:i:s').'['.implode(' ', $_SERVER['argv']).'] run not in cli');
}
//保证脚本同一时间只运行一次
/*$cache_key = md5(implode(' ', $_SERVER['argv']));
if (!Common_Fileflag::Factory($cache_key)->lock()) {
    //避免一个脚本被多次启用，实际上避免两个脚本并发启动，
    die(date('Y-m-d H:i:s')." {$_SERVER['argv'][0]} already runnning \n");
}*/
