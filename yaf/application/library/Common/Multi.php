<?php

/**
 * @package yindou
 * @brief  DB多主从拆分 基类
 * @author weixiaotong
 * @date   2016-3-29
 * @encoding UTF-8
 * @copyright (c) yindou
 */

class Common_Multi
{
   
    // 当前操作所属的模型名
    protected static $model      = 'yindou';
  
    // 错误信息
    protected static $error      = '';
    
    protected static $type       = '';
    
    protected static $linked =array(PDO::ATTR_PERSISTENT => true); //长链接
    
    protected static $slinked=array(PDO::ATTR_PERSISTENT =>false); //短连接  为默认的
    
    protected static $slave     =  's';
    
    private static $_check_type=array('r','w');
    
    /**
     * 取得数据库类实例
     * @static
     * @access public
     *
     * @return mixed 返回数据库驱动类
     */
    public static function getInstance($db_conf_name, $type='r')
    {
        $db_config=Model_Config::getDbConf($db_conf_name); //加载db配置文件
        if (empty($db_config)) {
            throw new Exception(Result::getErrCode(Errno::ERR_DB_CONFING_IS_EMPTY), Errno::ERR_DB_CONFING_IS_EMPTY);
        }
        
        self::$type=$type;
        
        $db_conf=    self::factory($db_config);
        static $_instance=array();
        $guid    = self::_to_guid_string($db_conf);
        if (!isset($_instance[$guid])) {
            $_instance[$guid]=     new Db_Pdo($db_conf);
        }
        return $_instance[$guid];
    }
    
    /**
     * 根据PHP各种类型变量生成唯一标识号
     * @param mixed $mix 变量
     * @return string
     */
    protected static function _to_guid_string($mix)
    {
        if (is_object($mix)) {
            return spl_object_hash($mix);
        } elseif (is_resource($mix)) {
            $mix = get_resource_type($mix) . strval($mix);
        } else {
            $mix = serialize($mix);
        }
        return md5($mix);
    }

    /**
     * 加载数据库 -处理数据库配置
     * @access public
     * @param mixed $db_config 数据库配置信息
     * @return string
     */
    public static function factory($db_config='')
    {
        // 读取数据库配置
        //新增 多主  多从 链接 方式
        $db_deploy_type=Model_Config::getDbGlobalConf('db_deploy_type');
        //根据开关 选择 是 多主多从 还是 单点
        if (!$db_deploy_type) {
            $db_config = self::parseConfig($db_config);
        } else {
            $db_config = self::parseMultiConfig($db_config);
        }
        
        return $db_config;
    }


    /**
     * 分析数据库配置信息，暂只支持数组
     * @access private
     * @param mixed $db_config 数据库配置信息
     * @return string
     */
    private static function parseConfig($db_config='')
    {
        if ($db_config['db_params']) {
            $db_params=self::$linked;
        } else {
            $db_params=self::$linked;
        }
        if (is_array($db_config) && !empty($db_config)) { // 数组配置
             $db_config =   array_change_key_case($db_config);
            $db_config = array(
                  'hostname'    =>  $db_config['dbhost'],
                  'hostport'    =>  $db_config['dbport'],
                  'database'    =>  $db_config['dbname'],
                  'username'    =>  $db_config['dbuser'],
                  'password'    =>  $db_config['dbpass'],
                  'params'      =>  $db_params,
             );
            return $db_config;
        }
        throw new Exception(Result::getErrCode(Errno::ERR_DB_CONFIG_INVILID), Errno::ERR_DB_CONFIG_INVILID);
    }
    
    
    /**
     * 分析数据库配置信息，支持数组  多主 多从 单独 处理dbconfig 配置信息
     * @access private
     * @param mixed $db_config 数据库配置信息
     * @return string
     */
    private static function parseMultiConfig($db_config='')
    {
        if (!is_array($db_config) || empty($db_config)) {
            throw new Exception(Result::getErrCode(Errno::ERR_DB_CONFIG_INVILID), Errno::ERR_DB_CONFIG_INVILID);
        }
        if (!in_array(self::$type, self::$_check_type)) {
            throw new Exception(Result::getErrCode(Errno::ERR_DB_CONFIG_INVILID), Errno::ERR_DB_CONFIG_INVILID);
        }
        if (is_array($db_config) && !empty($db_config)) { // 数组配置
                 
                if (isset($db_config['db_params']) && $db_config['db_params']) {
                    $db_params=self::$linked;
                } else {
                    $db_params=self::$slinked;
                }
            $db_config =   array_change_key_case($db_config);
                //判断type 是对主库操作，还是对从库操作
                if (self::$type=='r') {
                    $key=self::slaveRandKey();
                } else {
                    $key=self::masterRandKey();
                }
                //定义主库的分配规则
                //组合 主从
                $db_config = array(
                      'hostname'    =>  $db_config[$key]['dbhost'],
                      'hostport'    =>  $db_config[$key]['dbport'],
                      'database'    =>  $db_config[$key]['dbname'],
                      'username'    =>  $db_config[$key]['dbuser'],
                      'password'    =>  $db_config[$key]['dbpass'],
                      'dsn'         =>  $db_config[$key]['db_dsn'],
                      'params'      =>  $db_params,
                );
            return $db_config;
        } else {
            throw new Exception(Result::getErrCode(Errno::ERR_DB_CONFIG_INVILID), Errno::ERR_DB_CONFIG_INVILID);
        }
    }

    /**
     * 获取主库获取规则
     */
    private static function masterRandKey()
    {
        $m  =   floor(mt_rand(0, Model_Config::getDbGlobalConf('db_master_num')-1));
        $z_k='m_'.$m;
        return $z_k;
    }
    /**
     * 获取从库获取规则
     * @return string
     */
    private static function slaveRandKey()
    {
        $s  =   floor(mt_rand(0, Model_Config::getDbGlobalConf('db_slave_num')-1));
        $c_k='s_'.$s;
        return $c_k;
    }
}
