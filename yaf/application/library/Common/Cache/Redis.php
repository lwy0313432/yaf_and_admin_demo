<?php
/**
 *  redis基类
 *  只返回redis实例
 **/
class Common_Cache_Redis
{
    public $rds;  //连接成功时的标识
    private $rdscfg = null;
    private static $_instance = null; 


   public static  function getInstance() {
        if (is_null ( self::$_instance )) {
            self::$_instance = new self ();
        }
        return self::$_instance;
   } 
    /**
     * 初始化对象
     *
     * @return void
     **/
    public function __construct()
    {
        $_config = Yaf_Registry::get("config")->get("rds");
        $this->rdscfg = isset($_config['user']) ? $_config['user'] : null;
        if (!isset($this->rdscfg['host']) || !isset($this->rdscfg['port'])) {
            WLog::warning('redis conf is error '.json_encode($this->rdscfg), array(), 'redis');
            return false;
        }
        $this->rds=new Redis();
        if (@! $this->rds->connect($this->rdscfg['host'], $this->rdscfg['port'])) {
            WLog::warning('redis connect is error '.json_encode($this->rdscfg), array(), 'redis');
            return false;
        }
        //return $this->rds;
    }


    public function __call($func_name,$arguments){ 
        $log_pamar=array(
            'func_name'=>$func_name,
            'arguments'=>$arguments,
        );
        //redis-方法参数校验
        if(empty($arguments)  || empty($arguments) || !isset($arguments[0])){
            WLog::warning('redis use is error '.json_encode($log_pamar), array(), 'redis');
            return false;
        }
        $ret=call_user_func_array(array($this->rds,$func_name),$arguments);
        WLog::notice('redis use '.json_encode($log_pamar), array(), 'redis');
        return $ret;
    }

   

}
