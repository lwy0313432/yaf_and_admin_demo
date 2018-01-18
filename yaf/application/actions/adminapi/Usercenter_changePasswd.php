<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class Usercenter_changePasswdAction extends AdminApiBaseAction{
    public function beforeExecute(){
    }
    public function run($arg=null){
        $adminId = $this->getAdminId();
        $this->data = Admin::change_passwd($adminId,$_REQUEST);
    }
}
