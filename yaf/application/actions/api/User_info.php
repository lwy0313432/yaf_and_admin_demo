<?php
/**
 * @describe:个人信息页
 * @author: weixiaotong(weixt@yindou.com)
 * */
/* vim:set ts=4 sw=4 et fdm=marker: */
class user_infoAction extends ApiBaseAction{
    public function beforeExecute(){
        if (!$this->uid) {
            WLog::warning('uid is error'.json_encode(array('uid'=>$this->uid)), array(), 'user_info');
            throw new CException(Errno::USER_IS_NO_LOGIN_ERROR);
        }
    }
    public function run($args=null){
        $data=User::getUserInfo($this->uid);
        $this->data['list']=$data;
    }
}
