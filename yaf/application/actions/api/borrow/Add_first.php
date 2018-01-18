<?php
/**
 * @describe:添加借款信息-第一步
 * @author: weixiaotong(weixt@yindou.com)
 * */
/* vim:set ts=4 sw=4 et fdm=marker: */
class add_firstAction extends ApiBaseAction{
    private $realname = '';
    private $gender   = '';
    private $id_no    = '';
    private $email    = '';
    public function beforeExecute(){
        $this->realname        = $this->getRequestParam('realname', '');
        $this->gender          = $this->getRequestParam('gender', '');
        $this->id_no           = $this->getRequestParam('id_no', '');
        $this->email           = $this->getRequestParam('email','');
        if (!$this->uid) {
            WLog::warning('uid is error'.json_encode(array('uid'=>$this->uid)), array(), 'add_first');
            throw new CException(Errno::USER_IS_NO_LOGIN_ERROR);
        }
        if(intval($this->gender)==0){
            throw new CException(Errno::USER_GENDER_MUST_ERROR);
        }
        if(!Tools::is_id_card_num($this->id_no)){
            throw new CException(Errno::USER_ID_NO_IS_ERROR);
        }
        if(!Tools::is_email($this->email)){
            throw new CException(Errno::USER_EMAIL_IS_ERROR);
        }
    }
    public function run($args=null){
        $data=User::getAgentList($this->uid,$this->city_name,$this->start_time,$this->end_time,$this->page);
        $this->data['list']=$data;
    }
}
