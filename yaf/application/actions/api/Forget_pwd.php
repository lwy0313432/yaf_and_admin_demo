<?php
/**
 * @describe:个贷找回密码接口
 * @author: weixiaotong(weixt@yindou.com)
 * */
/* vim:set ts=4 sw=4 et fdm=marker: */
class Forget_pwdAction extends ApiBaseAction{
    private $mobile   = '';
    private $sms_code = '';
    private $new_pass     = '';
    public function beforeExecute(){
        $this->mobile       = $this->getRequestParam('mobile', '');
        $this->sms_code     = $this->getRequestParam('sms_code', '');
        $this->new_pass     = $this->getRequestParam('new_pass', '');
        $log_pamar=array(
            'mobile'   =>$this->mobile,
            'new_pass' =>$this->new_pass,
            'sms_code' =>$this->sms_code,
        );
        if (!Tools::is_mobile($this->mobile)) {
            WLog::warning('mobile is error '.json_encode($log_pamar), array(), 'forget_sms');
            throw new CException(Errno::USER_IS_MOBILE_ERROR);
        }
        if (!Tools::is_valid_passwd($this->pass)) {
            WLog::warning('pass is error '.json_encode($log_pamar), array(), 'forget_sms');
            throw new CException(Errno::USER_IS_PASS_ERROR);
        }
        //校验短信验证码
        User::checkSmsCode($this->mobile,Config::SMS_FORGET_TYPE,$this->sms_code);
    }
    public function run($args=null){
        $this->message = '重置密码成功';
        $ret=User::forgetPwd($this->mobile, $this->new_pass);
    }
}
