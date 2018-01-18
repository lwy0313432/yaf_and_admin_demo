<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
abstract class AdminApiBaseAction extends Yaf_Action_Abstract{
    abstract public function run($args=null);
    protected $code     = 0;
    protected $message  = '';
    protected $currentController   = null;
    protected $currentActionName  = null;
    private $adminId = 0;
    protected $data = null;
    public function getAdminId(){
        return $this->adminId;
    }
    private function _init(){
        if (Util::isProductEnv()) {
            $method = $this->getRequest()->getMethod();
            if ($method != 'POST') {
                throw new CException(Errno::INVALID_REQUEST_METHOD);
            }
        }else{//在测试环境，增加跨域开关
            header('Access-Control-Allow-Origin:http://localhost:8080');         
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie');
            header('Access-Control-Allow-Credentials:true'); 
        }
        $current_controller  = strtolower($this->getRequest()->getControllerName());   
        $current_action_name = strtolower($this->getRequest()->getActionName());    
        $this->current_controller  = $current_controller;
        $this->current_action_name = $current_action_name;
        $this->adminId = Admin::getAdminIdFromSession();
        AdminAuthCheck::check($this->adminId,$current_action_name); //action name 就是flag
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
            $this->jsonResponse();
        }catch(CException $e){
            $this->setException($e);
        }catch(Exception $e){
            $this->code    = $e->getCode();
            $this->message = $e->getMessage();
            $this->data    = new ArrayObject();
            return $this->jsonResponse();
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
    protected function setException(CException $ex)
    {
        $this->code    = $ex->getCode();
        $this->message = $ex->getMessage();
        return $this->jsonResponse();
    }
    protected function jsonResponse()
    {
        $res = array(
            'code'    => $this->code,
            'message' => $this->message,
            'data'    => is_null($this->data) ? new ArrayObject() : $this->data ,
        );

        $output = json_encode($res);
        if (isset($_REQUEST['callback']) && Util::checkVariableNameValidate($_REQUEST['callback'])) {
            $output = sprintf('%s(%s);', $_REQUEST['callback'], $output);
        }
        echo $output;
        return false;
    }
}
