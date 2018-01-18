<?php

/**
 * @package yindou
 * @brief 配置文件的读取 为了支持能够与Yaf解耦
 * @author weixiaotong
 * @date 2016-3-29
 * @encoding UTF-8
 * @copyright (c) yindou
 */

class Model_Config
{
    const RUN_ENV_MODE  =   'Yaf';

    public static function getConf($key)
    {
        if (self::RUN_ENV_MODE == 'Yaf') {
            return self::_getFromYafMode($key);
        }
    }
    
    protected static function _getFromYafMode($key)
    {
        //$config = Yaf_Registry::get('db');
        $config=Yaf_Registry::get("config")->get('db');
        if (null === $config) {
            return null;
        }
        if (is_object($config)) {
            return $config->toArray();
        }
        return $config;
    }
    
    public static function getDbGlobalConf($key)
    {
        $dbConfig = self::getConf('db');
        if (null === $dbConfig) {
            throw new Exception('please check your config file,lack db section');
        }
        
        if (!isset($dbConfig[$key])) {
            WLog::warning("config file,lack $key in db global section");
            return null;
        }
        return $dbConfig[$key];
    }
    
    public static function getDbConf($conf_db_name, $key = null, $dbName = '')
    {
        $dbConfig = self::getConf('db');

        if (null === $dbConfig) {
            throw new Exception('please check your config file,lack db section');
        }
        if (empty($dbName)) {
            $dbName = self::getDbGlobalConf($conf_db_name);
        }
        if (null === $dbName || !isset($dbConfig[$dbName]) || null === $dbConfig[$dbName]) {
            throw new Exception("please check your config file,lack $dbName in db section");
        }
        if (null === $key) {
            $config = $dbConfig[$dbName];
            $config['db_type'] = self::getDbGlobalConf('db_type');
            $config['db_charset'] = self::getDbGlobalConf('db_charset');
            return $config;
        }
        if (!isset($dbConfig[$dbName][$key])) {
            WLog::warning("check config file,lack $key in $dbName section");
            return null;
        }
        return $dbConfig[$dbName][$key];
    }
}
