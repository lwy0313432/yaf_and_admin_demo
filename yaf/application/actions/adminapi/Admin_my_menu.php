<?php
/*
 * 获取用户登录后左侧显示的菜单。
 */
class Admin_my_menuAction extends  AdminApiBaseAction 
{
    public static $default_passwd = 'yindouadmin001';
    public function run($arg=null)
    {
    
        $admin_id = $this->getAdminId();
        $admin_info=Admin::get_admin_info($admin_id);
        $default_passwd = md5(self::$default_passwd);

        $ret = AdminMenu::show_admin_menu($admin_id);
        if ($admin_info['admin_info']['password']==$default_passwd) {
            $result=array();
            if (!empty($ret['menu_list'])) {
                foreach ($ret['menu_list'] as $key=>$val) {
                    if ($val['flag']=='usercenter') {
                        $result[]=$val;
                    }
                }
            }
            
            $ret['menu_list']=$result;
        }
        $this->data=$ret;
    }
}
