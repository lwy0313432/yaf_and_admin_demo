<?php

/**
 * @package yindou
 * @brief API 验签 基类
 * @author weixiaotong <weixt@yindou.com>
 * @date 2017-12-19
 * @encoding UTF-8
 * @copyright (c) yindou
 */

class Util_Sign
{
    const  API_IOS                = '67d2aa116d7bb8ba77310d351debddfd'; //ios 系统参数 默认值
    const  API_ANDROID            = '71854e88b1a238bc572b63817decdert';//android 系统参数默认值
    
    
    const  DEFAULT_APP_SERECT_TIME='1462243202'; //默认 时间值
    
    
    
    //配置文件 app key
    private static function app_key_arr()
    {
        return array(
                self::API_IOS,
                self::API_ANDROID,
        );
    }
    
    //API参数校验校验规则 ，参数 以 数组 的形式
    
    public static function get_app_params_sign($params, $client_sign)
    {
        $system=self::app_key_arr();
        
        if (!isset($params['appserect'])) {
            throw new CException(Errno::ERR_APP_IS_PAMAR_SYSTEM);
        }
        if (!in_array($params['appserect'], $system)) {
            throw new CException(Errno::ERR_APP_IS_PAMAR_SYSTEM);
        }
        if ($params['appserect']==$system[0]) {
            $app_key=$system[0];
        } elseif ($params['keys']['appserect']==$system[1]) {
            $app_key=$system[0];
        } else {
            throw new CException(Errno::ERR_APP_IS_PAMAR_SYSTEM);
        }
        $sign=isset($params['sign'])?$params['sign']:'';
        unset($params['sign']);
        krsort($params);
        $str = '';
        foreach($params as $key=>$val){
            $str .= $key . '=' . $val . '&';
        }
        $str .= $app_key;
        $server_sign = md5(sha1($str));
        return  true;
    }
}
