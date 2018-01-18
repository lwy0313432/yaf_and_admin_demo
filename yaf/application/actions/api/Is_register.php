<?php
/**
 * @describe:个贷注册接口
 * @author: weixiaotong(weixt@yindou.com)
 * */
/* vim:set ts=4 sw=4 et fdm=marker: */
class is_registerAction extends ApiBaseAction{
    private $mobile   = '';
    private $sms_code = '';
    private $pass     = '';
    private $city_name= '';
    private $r_code   = '';
    public function beforeExecute(){
        $this->mobile       = $this->getRequestParam('mobile', '');
        $this->sms_code     = $this->getRequestParam('sms_code', '');
        $this->pass         = $this->getRequestParam('pass', '');
        $this->city_name    = $this->getRequestParam('city_name', '');
        $this->r_code       = $this->getRequestParam('r_code', '');
        $log_pamar=array(
            'mobile'   =>$this->mobile,
            'pass'     =>$this->pass,
            'r_code'   =>$this->r_code,
            'sms_code' =>$this->sms_code,
            'city_name'=>$this->city_name,
        );
        if (!Tools::is_mobile($this->mobile)) {
            WLog::warning('mobile is error '.json_encode($log_pamar), array(), 'register');
            throw new CException(Errno::USER_IS_MOBILE_ERROR);
        }
        if (!Tools::is_valid_passwd($this->pass)) {
            throw new CException(Errno::USER_IS_PASS_ERROR);
        }
        if(empty($this->city_name)){
            throw new CException(Errno::USER_IS_CITY_ERROR);
        }
        //校验短信验证码
        User::checkSmsCode($this->mobile,Config::SMS_REGISTER_TYPE,$this->sms_code);
    }
    public function run($args=null){
        $this->message = '注册成功';
        $data=User::addUser($this->mobile,$this->pass, $this->city_name, $this->r_code);
        $this->data['list']=$data;
    }
}
