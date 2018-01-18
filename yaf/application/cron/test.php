<?php
use Cloudauth\Request\V20171117\GetStatusRequest;
/**
 * @package yindou
 * @brief  test
 * @author weixiaotong <weixt@yindou.com>
 * @date 2017-12-20
 * @encoding UTF-8
 * @copyright (c) yindou
 */
set_time_limit(0);
require_once('common.php');
$obj=new SendApi();
//$ret=$obj->getStatus();
$ret=$obj->getVerifyToken();
