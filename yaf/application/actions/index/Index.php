<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class indexAction extends WebBaseAction{
    public function beforeExecute(){
    }
    public function run($args=null){
        $userObj  = new User();
        $uid= $this->getUid();
        $uid=1;
        $this->data = $userObj->getUserInfo($uid);
    }
}
