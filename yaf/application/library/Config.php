<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class Config{
    
    const DATA_DEFAULT_LIMIT =10;//前端app默认就每页10条吧
    //用户相关
    
    const USER_TYPE_IS_CLOSE=0;//正常状态
    const USER_TYPE_IS_CLOSE_STOP=1;//被禁用
    
    //短信相关
    const SMS_IS_YMRT      = 1;   //亿美软通
    const SMS_IS_MLRT      = 2;   //美联软通
    const SMS_IS_CLZG      = 3;   //创蓝中国
    const SMS_IS_YMRT_MART = 4;  //亿美软通营销短信
    const SMS_IS_CLZG_MART = 5;  //创蓝中国营销短信
    const SMS_IS_CLZG_VOICE= 6;  //创蓝中国语音短信
    const SMS_IS_KJCX_MART = 7;  //空间畅想营销短信
    
    //短信发送类型
    const SMS_REGISTER_TYPE= 1;  //注册
    const SMS_FORGET_TYPE  = 2;  //找回密码
    
    const MSG_VOICE='voice';//语音
    const MSG_TEXT ='text'; //文本
    
    const  SMS_CHANNEL_IS_MARKET=2; //2为营销短信
    const  SMS_CHANNEL_IS_NOTICE=1; //1为通知短信
    
    //是否需要生成验证码
    const SMS_DEFAULT_LEFT_TIMES=5; //默认left_times
    const SMS_IS_NEED_CODE=1; //需要生成短信验证码
    const SMS_IS_NO_NEED_CODE=2;//不需要生成短信验证码
    const MOBILE_CODE_EXPIRED_TIME=10;//验证码存活时间为10分钟
    const SERVICE_MOBILE   ='4000-586-587';//客服电话
    
    const SMS_IS_REDIS_CONFIG_KEY='person_sms';
    //每天最多5条
    const SMS_IS_MAX_TODAY=5;
    //短信状态
    const SMS_EXPIRED_TYPE   =1;
    const SMS_NO_EXPIRED_TYPE=0;
    //短信发送状态
    const SMS_STATUS_IS_SUCCESS=1;
    
    const SMS_STATUS_IS_FAILD  =-1;
    public static function get_sms_type()
    {
        return array(
            self::SMS_REGISTER_TYPE,//注册
            self::SMS_FORGET_TYPE,  //找回密码
        );
    }
    
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
