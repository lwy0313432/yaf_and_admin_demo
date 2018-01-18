<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class indexAction extends AdminBaseAction{
    public function beforeExecute(){
    }
    public function run($args=null){
        //$adminInfo = Admin::getAdminInfo(1);
        $this->display('admin/index.tpl');
    }
}
