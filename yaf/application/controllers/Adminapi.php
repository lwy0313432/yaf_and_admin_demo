<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class AdminapiController extends Yaf_Controller_Abstract{
    public $actions=array(
        'dologin'=>'actions/adminapi/Dologin.php',
        'logout'=>'actions/adminapi/Logout.php',
        'islogin'=>'actions/adminapi/Islogin.php',
        'index'=>'actions/adminapi/Index.php',
        'admin_my_menu'=>'actions/adminapi/Admin_my_menu.php',
        'getAdminInfo'=>'actions/adminapi/GetAdminInfo.php',
        'authority_menu_list'=>'actions/adminapi/Authority_menu_list.php',
        'authority_menu_add'=>'actions/adminapi/Authority_menu_add.php',
        'authority_menu_edit'=>'actions/adminapi/Authority_menu_edit.php',
        'authority_menu_del'=>'actions/adminapi/Authority_menu_del.php',
        'authority_admin_list'=>'actions/adminapi/Authority_admin_list.php',
        'authority_admin_add'=>'actions/adminapi/Authority_admin_add.php',
        'authority_admin_getinfo'=>'actions/adminapi/Authority_admin_getinfo.php',
        'authority_admin_update'=>'actions/adminapi/Authority_admin_update.php',
        'authority_admin_del'=>'actions/adminapi/Authority_admin_del.php',
        'usercenter_changepasswd'=>'actions/adminapi/Usercenter_changePasswd.php',
    );
}
