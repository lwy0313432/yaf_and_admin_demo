<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
abstract class WebBaseAction extends Yaf_Action_Abstract{
    abstract public function run($args=null);
    protected $code     = Errno::SUCCESS;
    protected $message  = '';
    protected $currentController   = null;
    protected $currentActionName  = null;
    private $uid = 0;
    protected $data = null;
    public function getUid(){
        return $this->uid;
    }
    private function _init(){
        $current_controller  = strtolower($this->getRequest()->getControllerName());   
        $current_action_name = strtolower($this->getRequest()->getActionName());    
        $this->current_controller  = $current_controller;
        $this->current_action_name = $current_action_name;
        $this->uid = User::getUidFromSession();
        if (Util::isProductEnv()) {
            $method = $this->getRequest()->getMethod();
            if ($method != 'POST') {
                throw new CException(Errno::INVALID_REQUEST_METHOD);
            }
        }
    }
    protected function beforeExecute(){
        return true;
    }
    protected function afterExecute(){
        return true;
    }
    public function execute($arg = null){
        try{
            $this->_init();
            $this->beforeExecute();
            $this->run($arg);
            $this->afterExecute();
        }catch (Exception $e) {
            $this->assign('show_error', Util::isProductEnv() ? 0 : 1); 
            $this->code    = $e->getCode();
            $this->message = $e->getMessage();
            $this->assign('errMsg',$this->message);
            $this->display('error/error.tpl');
        }catch(CException $e){
            $this->assign('show_error', Util::isProductEnv() ? 0 : 1); 
            $this->code    = $e->getCode();
            $this->message = $e->getMessage();
            $this->assign('errMsg',$this->message);
            $this->display('error/error.tpl');
        }
    }
    /**
    @param $name
    @param string $defaultValue
    @return string
    */
    public function getParam($name, $default = '')
    {
        return isset($_GET[$name]) ? Util::escape($_GET[$name]) : $default;
    }
    /**
    * @param $name
    * @param string $defaultValue
    * @return string
    */
    public function postParam($name, $default = '')
    {
        return isset($_POST[$name]) ? Util::escape($_POST[$name]) : $default;
    }
    /**
        * @param $name
        * @param string $defaultValue
        * @return string
     */
    public function getRequestParam($name, $default = '')
    {
        return isset($_REQUEST[$name]) ? Util::escape($_REQUEST[$name])
            : (isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default);
    }

    public function postUnescape($name, $default = '')
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }
    // 简化 assign 模板变量的操作
    public function assign($key, $value)
    {   
        $this->getView()->assign($key, $value);
    }
    // 简写,要写太多的长代码
    public function display($tpl, array $parameters = null)                                                                                    
    {
        $this->getView()->display($tpl);
    }
}
