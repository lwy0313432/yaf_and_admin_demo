<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class IsloginAction extends AdminApiBaseAction{
    public function run($args=null){
        $this->data = Admin::isLogin();
    }
}
