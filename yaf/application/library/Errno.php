<?php

/**
 * @package Library
 * @brief  全局错误号，统一错误号处理
 * @author weixiaotong <weixt@yindou.com>
 * @date 2016-03-17
 * @encoding UTF-8
 * @copyright (c) yindou
 */

class Errno
{
    const SUCCESS    =   0;
    const CODE_DOES_NOT_EXIST = 1;    
    const INVALID_REQUEST_METHOD=2;
    const ERR_SERVICE_NOT_EXIST = 100000;
    const ERR_SERVICE_IS_EMPTY  = 100001;
    const ERR_SERVICE_METHOD_NOT_EXIST  = 100002;
    const ERR_SERVICE_METHOD_IS_EMPTY   = 100003;
    const ERR_INPUT_PARAMS_INVALID  = 100004;

    const ERR_DB_TABLE_SCHEMA_NOT_DEFINED = 200001;
    const ERR_DB_MODEL_METHOD_NOT_EXIST   = 200002;
    const ERR_DB_DATA_INVILID             = 200003;
    const ERR_DB_ADD_FAILED               = 200004;
    const ERR_DB_CONDITION_INVILID        = 200005;
    const ERR_DB_CONFIG_INVILID           = 200006;
    const ERR_DB_TYPE_NOT_DEFINE          = 200007;
    const ERR_DB_QUERY_FAILED             = 200008;
    const ERR_DB_RECORD_REPEAT            = 200009;
    const ERR_DB_TRANS_FAILED             = 200010;
    const ERR_DB_RECORD_DEL_DENY          = 200011;
    const ERR_DB_CONFING_IS_EMPTY         = 200012;
    const DB_ERROR                        = 200013;  
    
    const PARAM_INVALID = 400001;                   //参数错误
    const ERR_USER_OPERATE_FORIDDEN       = 400003;
    const ERR_APP_IS_PAMAR_SYSTEM         = 400004;//系统参数错误
    const USER_IS_MOBILE_ERROR            = 400005;//手机号格式错误
    const USER_IS_PASS_ERROR              = 400006;//密码格式错误
    const USER_IS_CITY_ERROR              = 400007;//城市不正确
    const USER_IS_RCODE_ERROR             = 400008;//邀请码不正确
    const USER_IS_SMS_CODE_ERROR          = 400009;//短信验证码不正确

    const USER_SEND_SMS_TYPE_ERROR        = 400010;//短信发送类型不正确
    const USER_GET_SMS_INFO_ERROR         = 400011;//获取短信验证码失败
    const USER_GET_SMS_CODE_ERROR         = 400012;//短信验证码不正确
    const USER_GET_SMS_CODE_EXPIRE_ERROR  = 400013;//短信验证码已过期
    const USER_GET_SMS_CONTENT_ERROR      = 400013;//短信内容不能为空
    const USER_SEND_SMS_MAX_ERROR         = 400014;//每天最多发送5条
    const USER_SEND_SMS_IS_ERROR          = 400015;//短信发送失败
	const VCODE_ERR                       = 400016;
	const USER_IS_REGISTER_ERROR          = 400017;//注册失败
	const USER_IS_MOBILE_REGISTER_ERROR   = 400018;//手机号已注册
	const USER_IS_USERNAME_ERROR          = 400019;//用户名密码不正确
	const USER_MOBILE_NO_REG_ERROR        = 400020;//手机号未注册
	const USER_PASS_AS_OLD_ERROR          = 400021;//新密码和原密码一致
	const USER_PASS_RESET_ERROR           = 400022;//重置密码失败
	const USER_IS_NO_LOGIN_ERROR          = 400023;//请先登录
	const USER_IS_NEW_PASS_ERROR          = 400024;//新密码格式不正确
	const USER_IS_OLD_PASS_ERROR          = 400025;//原密码不正确
	const USER_START_GT_END_ERROR         = 400026;//开始时间不能大于结束时间
	const USER_GENDER_MUST_ERROR          = 400027;//性别为必选项
	const USER_ID_NO_IS_ERROR             = 400028;//身份证格式错误
	const USER_EMAIL_IS_ERROR             = 400029;//邮箱格式不正确
	const USER_UPLOAD_FILE_IS_ERROR       = 400030;//上传图片格式不正确
	const USER_UPLOAD_IMG_IS_ERROR        = 400031;//上传图片失败
	

    

    const ADMIN_NOT_EXIST = 500001;   
    const ADMIN_PERMISSION_DENY = 500002;   
    const ADMIN_NOT_LOGIN = 500003;
    const ADMIN_ID_PARAM_NOT_SET = 500004;
    const ADMIN_MENU_NOT_EXIST = 500005;   
    const ADMIN_MENU_HAVE_SUB_MENU = 500006;
    const ADMIN_MENU_FLAG_ALREADY_EXIST = 500007;
    const ADMIN_MENU_PARENT_NOT_EXIST = 500008;
    const ADMIN_MENU_FLAG_NOT_VALID = 500009;
    const ADMIN_MENU_FLAG_NOT_VALID_1=500010;
    const ADMIN_MENU_FLAG_NOT_VALID_2=500011;
    const ADMIN_MENU_FLAG_NOT_VALID_3=500012;
    const ADMIN_MENU_EDIT_NOT_VALID  =500013; 
    const ADMIN_MENU_EDIT_NOT_VALID_2  =500014; 
    const ADMIN_MENU_NAME_NOT_VALID = 500015;
    const ADMIN_MENU_ADD_FLAG_NOT_VALID = 500016;
    const ADMIN_MENU_ADD_IS_DISPLAY_NOT_VALID = 500017;
    const ADMIN_MENU_ADD_NOT_VALID = 500018;
    const ADMIN_USERNAME_INVALID = 500019;
    const ADMIN_USERNAME_ALREADY_EXIST=500020;
    const ADMIN_PASSWORD_INVALID=500021;
    const ADMIN_ROLE_INVALID=500022;
    const ADMIN_REALNAME_INVALID=500023;
    const ADMIN_OLD_PASSWD_ERR = 500024;
    const ADMIN_USERNAME_OR_PASSWORD_ERR = 500025;
    public static $codes = array(
        self::SUCCESS       =>  '成功',
        self::CODE_DOES_NOT_EXIST => '错误码不存在',
        self::INVALID_REQUEST_METHOD=>'请求的method错误',
        self::ERR_SERVICE_NOT_EXIST  =>  '服务不存在',
        self::ERR_SERVICE_IS_EMPTY  =>  '服务名不能为空',
        self::ERR_SERVICE_METHOD_NOT_EXIST  =>  '服务方法不存在',
        self::ERR_SERVICE_METHOD_IS_EMPTY  =>  '服务方法不能为空',
        self::ERR_INPUT_PARAMS_INVALID  =>  '参数无效',
        self::ERR_DB_TABLE_SCHEMA_NOT_DEFINED  =>  '数据库表结构字段未定义',
        self::ERR_DB_MODEL_METHOD_NOT_EXIST  =>  '数据模型中未定义此方法',
        self::ERR_DB_DATA_INVILID  =>  '操作记录的内容不能为空',
        self::ERR_DB_ADD_FAILED  =>  '插入记录失败',
        self::ERR_DB_CONDITION_INVILID  =>  '数据库执行条件不合法',
        self::ERR_DB_CONFIG_INVILID  =>  '数据库配置信息错误',
        self::ERR_DB_TYPE_NOT_DEFINE  =>  '数据库类型没有被定义',
        self::ERR_DB_QUERY_FAILED  =>  '数据库操作失败',
        self::ERR_DB_RECORD_REPEAT  =>  '数据库记录重复',
        self::ERR_DB_TRANS_FAILED  =>  '数据库事务失败',
        self::ERR_DB_RECORD_DEL_DENY  =>  '该条数据记录不允许删除',
        self::ERR_DB_CONFING_IS_EMPTY  =>  '加载数据库配置文件失败',
        self::PARAM_INVALID =>'参数错误',
        self::ERR_USER_OPERATE_FORIDDEN  =>  '用户操作被禁止',
        self::ADMIN_NOT_EXIST =>'管理员不存在',
        self::ADMIN_PERMISSION_DENY=>'管理员权限不足',
        self::ADMIN_NOT_LOGIN=>'管理员未登录',
        self::ADMIN_ID_PARAM_NOT_SET=>'admin_id参数未设置',
        self::ADMIN_MENU_NOT_EXIST=>'admin menu不存在',
        self::ADMIN_MENU_HAVE_SUB_MENU=>'该菜单有子菜单，不能删除',
        self::DB_ERROR=>'数据库操作错误',
        self::ADMIN_MENU_FLAG_ALREADY_EXIST=>'admin_menu 的flag已存在',
        self::ADMIN_MENU_PARENT_NOT_EXIST=>'父菜单不存在',
        self::ADMIN_MENU_FLAG_NOT_VALID=>'添加的菜单flag不符合规范',
        self::ADMIN_MENU_FLAG_NOT_VALID_1=>'菜单flag不符合规范，必须以父菜单flag加_开头',
        self::ADMIN_MENU_FLAG_NOT_VALID_2=>'菜单flag不符合规范，下划线后面必须有字符',
        self::ADMIN_MENU_FLAG_NOT_VALID_3=>'菜单flag不符合规范,real_flag不能有下划线',
        self::ADMIN_MENU_EDIT_NOT_VALID=>'存在子菜单的is_display=y，所以当前菜单的is_display不能为n',
        self::ADMIN_MENU_EDIT_NOT_VALID_2=>'父菜单的is_display=n，所以当前菜单的is_display不能为y',
        self::ADMIN_MENU_NAME_NOT_VALID =>'menu_name不合法',
        self::ADMIN_MENU_ADD_FLAG_NOT_VALID=>'menu_add_flag不合法',
        self::ADMIN_MENU_ADD_IS_DISPLAY_NOT_VALID=>'is_display不合法',
        self::ADMIN_MENU_ADD_NOT_VALID=>'父菜单是不展示的，不能添加子菜单,请先编辑父节点',
        self::ADMIN_USERNAME_INVALID=>'用户名不合法',
        self::ADMIN_USERNAME_ALREADY_EXIST=>'用户名已存在',
        self::ADMIN_PASSWORD_INVALID=>'密码不合法，必须包含大写字母，小写字母，特殊字符，数字，8位以上',
        self::ADMIN_ROLE_INVALID=>'用户角色设置不对',
        self::ADMIN_REALNAME_INVALID=>'姓名设置不合法',
        self::ADMIN_OLD_PASSWD_ERR=>'修改密码，旧密码错误',
        self::ADMIN_USERNAME_OR_PASSWORD_ERR=>'用户名或者密码错误',
       
        /******前端信息提示*******/
        self::ERR_APP_IS_PAMAR_SYSTEM       =>'系统参数错误',
        self::USER_IS_MOBILE_ERROR          =>'手机号格式错误',
        self::USER_IS_PASS_ERROR            =>'密码格式错误',
        self::USER_IS_CITY_ERROR            =>'城市不能为空',
        self::USER_IS_RCODE_ERROR           =>'邀请码不正确',
        self::USER_IS_SMS_CODE_ERROR        =>'短信验证码不正确',
        self::USER_SEND_SMS_TYPE_ERROR      =>'短信发送类型不正确',
        self::USER_GET_SMS_INFO_ERROR       =>'获取短信验证码失败',
        self::USER_GET_SMS_CODE_EXPIRE_ERROR=>'短信验证码已过期',
        self::USER_SEND_SMS_IS_ERROR        =>'短信发送失败',
	    self::VCODE_ERR                     =>'图片验证码错误',
        self::USER_IS_REGISTER_ERROR        =>'注册失败',
        self::USER_IS_MOBILE_REGISTER_ERROR =>'手机号已注册',
        self::USER_IS_USERNAME_ERROR        =>'用户名密码不正确',
        self::USER_SEND_SMS_MAX_ERROR       =>'每天最多发送5条',
        self::USER_MOBILE_NO_REG_ERROR      =>'手机号未注册',
        self::USER_PASS_AS_OLD_ERROR        =>'新密码和原密码一致',
        self::USER_PASS_RESET_ERROR         =>'重置密码失败',
        self::USER_IS_NO_LOGIN_ERROR        =>'请先登录',
        self::USER_IS_NEW_PASS_ERROR        =>'新密码格式不正确',
        self::USER_IS_OLD_PASS_ERROR        =>'原密码错误',
        self::USER_START_GT_END_ERROR       =>'开始时间不能大于结束时间',
        self::USER_GENDER_MUST_ERROR        =>'性别为必选',
        self::USER_ID_NO_IS_ERROR           =>'身份证格式错误',
        self::USER_UPLOAD_FILE_IS_ERROR     =>'上传图片格式不正确',
        self::USER_UPLOAD_IMG_IS_ERROR      =>'上传图片失败',
    );
    public static function getMessage($code){
        $message = '';
        if (! isset(self::$codes[$code])) {
            throw new CException(self::CODE_DOES_NOT_EXIST);
        }else {
            $message = self::$codes[$code];
        }
        return $message;
    }
}
