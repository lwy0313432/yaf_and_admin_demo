<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class Config{
    
  
    const  UPLOAD_URL="miyouimg.b0.aicdn.com/";
    
    
    const  PHOTO_TYPE_1=1; //首次照片上传
    const  PHOTO_TYPE_2=2; //第二次照片上传
    const  UPLOAD_DEFAULT_TITLE='上传用户身份证';
    
    //短信相关end
    //喉管管理系统中不需要登录的接口。
    public static $adminNotNeedAuthController=array(
        'dologin',
        'islogin',
        'logout',
        'admin_my_menu',
        'usercenter_changepasswd',
    );
    private static $routeRule=array(
        'hdgg_page'=>array(
            'regex'=>'#/hdgg/page_(\d)\.html#',
            'control'=>array('module'=>'Index', 'controller'=>'hdgg', 'action'=>'page', ),
            'param'=>array(1=>'page',),
        ),
    );
    public static function getCustomerRoute(){
        return self::$routeRule;
    }
    public static function getSmartyConf(){   
        $smarty = array(
            'left_delimiter'  => '<{',
            'right_delimiter' => '}>',
            'template_dir' => APPLICATION_PATH . '/application/views/',
            'compile_dir'  => APPLICATION_PATH . '/application/cache/smarty_compile',
            'cache_dir'    => APPLICATION_PATH . '/application/cache/smarty_cache',
        );
        return $smarty;
    }
}
