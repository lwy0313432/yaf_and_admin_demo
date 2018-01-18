<?php
/**
 * @describe:短信发送接口
 * @author: weixiaotong(weixt@yindou.com)
 * */
/* vim:set ts=4 sw=4 et fdm=marker: */
class sms_sendAction extends ApiBaseAction{
    private $mobile   = '';
    private $type     = '';
    public function beforeExecute(){
        $this->mobile       = $this->getRequestParam('mobile', '');
        $this->type         = $this->getRequestParam('type', '');
        $log_pamar=array(
            'mobile'   =>$this->mobile,
            'pass'     =>$this->type,
        );
        if (!Tools::is_mobile($this->mobile)) {
            WLog::warning('mobile is error '.json_encode($log_pamar), array(), 'register');
            throw new CException(Errno::USER_IS_MOBILE_ERROR);
        }
        //校验手机号是否已注册
        if($this->type==Config::SMS_REGISTER_TYPE){
            User::getUidByMobile($this->mobile);
        }
        //校验短信类型是否正确合法
        $sms_type_arr=Config::get_sms_type();
        if (!in_array($this->type, $sms_type_arr)) {
            throw new CException(Errno::USER_SEND_SMS_TYPE_ERROR);
        }
    }
    public function run($args=null){
        $ret=Util_Sms::send_sms($this->mobile, $this->type);
        $this->message = '发送成功，请查收';
    }
}
