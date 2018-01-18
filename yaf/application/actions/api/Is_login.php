<?php
/**
 * @describe:个贷登录接口
 * @author: weixiaotong(weixt@yindou.com)
 * */
/* vim:set ts=4 sw=4 et fdm=marker: */
class is_loginAction extends ApiBaseAction{
    private $username   = '';
    private $pass       = '';
    public function beforeExecute(){
        $this->username        = $this->getRequestParam('username', '');
        $this->pass            = $this->getRequestParam('pass', '');
        $log_pamar=array(
            'username'=>$this->username,
            'pass'     =>$this->pass,
        );
        //现在的用户名就是手机号 ，所以
        if (!Tools::is_mobile($this->username)) {
            WLog::warning('username is error '.json_encode($log_pamar), array(), 'login');
            throw new CException(Errno::USER_IS_MOBILE_ERROR);
        }
        if (empty($this->pass)) {
            throw new CException(Errno::USER_IS_PASS_ERROR);
        }
    }
    public function run($args=null){
        $this->message = '登录成功';
        $data=User::login($this->username, $this->pass);
        $this->data['list']=$data;
    }
}
