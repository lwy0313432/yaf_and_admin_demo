<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
abstract class ApiBaseaction extends Yaf_Action_Abstract{
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
        //app统一验签--test中
        //Util_Sign::get_app_params_sign($_POST);
        //在这直接把uid
        $token=$this->getRequestParam('token', 0);
        $this->uid = User::getUidFromCache($token);
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
