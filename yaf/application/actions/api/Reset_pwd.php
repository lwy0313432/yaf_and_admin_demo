<?php
/**
 * @describe:重置密码
 * @author: weixiaotong(weixt@yindou.com)
 * */
/* vim:set ts=4 sw=4 et fdm=marker: */
class reset_pwdAction extends ApiBaseAction{
    private $old_pass   = '';
    private $new_pass   = '';
    public function beforeExecute(){
        if (!$this->uid) {
            WLog::warning('uid is error'.json_encode(array('uid'=>$this->uid)), array(), 'reset_pwd');
            throw new CException(Errno::USER_IS_NO_LOGIN_ERROR);
        }
        if (!Tools::is_valid_passwd($this->old_pass)) {
            throw new CException(Errno::USER_IS_PASS_ERROR);
        }
        if (!Tools::is_valid_passwd($this->new_pass)) {
            throw new CException(Errno::USER_IS_NEW_PASS_ERROR);
        }
    }
    public function run($args=null){
        $this->message = '重置密码成功';
        $data=User::resetPwd($this->uid,$this->old_pass,$this->new_pass);
    }
}