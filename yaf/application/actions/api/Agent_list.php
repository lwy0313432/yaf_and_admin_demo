<?php
/**
 * @describe:推荐人列表
 * @author: weixiaotong(weixt@yindou.com)
 * */
/* vim:set ts=4 sw=4 et fdm=marker: */
class agent_listAction extends ApiBaseAction{
    private $start_time = '';
    private $end_time   = '';
    private $city_name  = '';
    private $page       = '';
    public function beforeExecute(){
        $this->start_time      = $this->getRequestParam('start_time', '');
        $this->end_time        = $this->getRequestParam('end_time', '');
        $this->page            = intval($this->getRequestParam('page', 1));
        $this->city_name       = $this->getRequestParam('city_name','');
        if (!$this->uid) {
            WLog::warning('uid is error'.json_encode(array('uid'=>$this->uid)), array(), 'agent_list');
            throw new CException(Errno::USER_IS_NO_LOGIN_ERROR);
        }
        if(!empty($this->start_time) || !empty($this->end_time)){
            if(strtotime($this->start_time) < strtotime($this->end_time)){
                throw new CException(Errno::USER_START_GT_END_ERROR);
            }
        }
    }
    public function run($args=null){
        $data=User::getAgentList($this->uid,$this->city_name,$this->start_time,$this->end_time,$this->page);
        $this->data['list']=$data;
    }
}
