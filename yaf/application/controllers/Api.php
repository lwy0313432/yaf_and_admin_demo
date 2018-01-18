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
        'index'      =>'actions/api/Index.php',
        'is_register'=>'actions/api/Is_register.php',//注册
        'is_login'   =>'actions/api/Is_login.php',   //登录
        'sms_send'   =>'actions/api/Sms_send.php',   //短信发送
        'forget_pwd' =>'actions/api/Forget_pwd.php', //忘记密码
        'user_info'  =>'actions/api/User_info.php',  //用户信息
        'reset_pwd'  =>'actions/api/Reset_pwd.php',  //重置密码
        'agent_list' =>'actions/api/Agent_list.php', //推荐人列表
        'upload'     =>'actions/api/borrow/Upload.php', //上传身份证信息
    );
}
