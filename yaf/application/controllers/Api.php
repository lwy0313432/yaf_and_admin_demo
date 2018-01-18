<?php
/**
 * @describe:app端所有的接口都放到这里面
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class ApiController extends Yaf_Controller_Abstract{
    /*
    public function indexAction(){
        echo 'index';
        return false;
    }
     */
    public $actions=array(
        'is_register'=>'actions/api/Is_register.php',//注册
    );
}
