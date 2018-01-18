<?php                                                                                                                      
/**
 *  * @describe:
 *   * @author: liuwy(liuwy@yindou.com)
 *    * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class IndexAction extends WebBaseAction{
    public function beforeExecute(){
    }
    public function run($args=null){
        $uid = $this->getUid();
        $this->assign('uid',$uid);
        $this->display('web/index.tpl'); 
    }
}
