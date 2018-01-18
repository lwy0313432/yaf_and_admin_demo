<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class indexAction extends AdminApiBaseAction{
    public function beforeExecute(){
        
    }
    public function run($args=null){
        $this->code=Errno::SUCCESS;
        $this->message = Errno::getMessage($this->code);
        $this->data=array('id'=>1,'name'=>'hell');
    }
}
