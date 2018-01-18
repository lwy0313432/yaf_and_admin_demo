<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */

class User{
    public static function getUidFromSession(){
        $uid = 0;
        if(isset($_SESSION['uid']) && is_numeric($_SESSION['uid']) && $_SESSION['uid'] >0){
            $uid=$_SESSION['uid'];
        }
        return $uid;
    }
    //app专用
    public static function getUidFromCache($token){
        if(!$token){
            return intval($token);
        }
        $user_info=Tools::decry_lender_token($token);
        return $user_info['uid'];
    }
    //校验用户邀请码是否真实有效
    public static function checkRcode($r_code){
        if(empty($r_code)){
            throw new CException(Errno::USER_IS_RCODE_ERROR);
        }
        $dao_user=new Dao_Default_UserModel();
        $user_info=$dao_user->where(array('mobile'=>$r_code))->find();
        if(!$user_info){
            throw new CException(Errno::USER_IS_RCODE_ERROR);
        }
        return $user_info;
    }
    //校验用户的短信验证码是否正确
     public static function checkSmsCode($mobile,$type,$sms_code){
         if(empty($sms_code) || intval($sms_code)==0){
             throw new CException(Errno::USER_IS_SMS_CODE_ERROR);
         }
         $ret=Util_Sms::get_sms_result_info($mobile, $type, $sms_code);
         return $ret;
     }
     //校验手机号是否已注册
     public static function getUidByMobile($mobile){
         if(!$mobile){
             WLog::warning('mobile is error '.json_encode(array('mobile'=>$mobile)), array(), 'register');
             throw new CException(Errno::USER_IS_MOBILE_ERROR);
         }
         $dao_user=new Dao_Default_UserModel();
         $user_info=$dao_user->where(array('mobile'=>$mobile))->find();
         if($user_info){
             throw new CException(Errno::USER_IS_MOBILE_REGISTER_ERROR);
         }
         return true;
     }
     //登录
     public static function login($username,$pass){
         if(empty($username)){
             throw new CException(Errno::USER_IS_USERNAME_ERROR);
         }
         if(empty($pass)){
             throw new CException(Errno::USER_IS_USERNAME_ERROR);
         }
         $dao_user=new Dao_Default_UserModel();
         $where_arr=array(
             'username'=>$username,
             'password'=>md5($pass),
         );
         $user_info=$dao_user->where($where_arr)->find();
         if(!$user_info){
             WLog::warning('login xx is error '.json_encode($where_arr), array(), 'login');
             throw new CException(Errno::USER_IS_USERNAME_ERROR);
         }
         //登录成功以后 给用户生成token=返回给app
         $token_info=Tools::encrypt_lender_token($user_info['id'], $user_info['mobile']);
         $data['token']    =$token_info['token'];
         return $data;
     }
     //注册   
     public static function addUser($mobile,$pass,$city_name,$r_code){
       if (!Tools::is_mobile($mobile)) {
            WLog::warning('mobile is error '.json_encode(array('mobile'=>$mobile)), array(), 'register');
            throw new CException(Errno::USER_IS_MOBILE_ERROR);
        }
        if (!Tools::is_valid_passwd($pass)) {
            WLog::warning('pass is error '.json_encode(array('pass'=>$pass)), array(), 'register');
            throw new CException(Errno::USER_IS_PASS_ERROR);
        }
        if(empty($city_name)){
            WLog::warning('city is error '.json_encode(array('city_name'=>$city_name)), array(), 'register');
            throw new CException(Errno::USER_IS_CITY_ERROR);
        }
        //校验手机号是否已注册
        self::getUidByMobile($mobile);
        $parent_info=array();
        if($r_code){
            $parent_info=self::checkRcode($r_code);
        }
        $pid=isset($parent_info['id'])?$parent_info['id']:0;
        $dao_user=new Dao_Default_UserModel();
        $dao_user_agent=new Dao_Default_UserAgentModel();
        $in_user_arr=array(
            'username'=>$mobile,//现在先默认为手机号
            'password'=>md5($pass),
            'mobile'  =>$mobile,
            'city'    =>$city_name,
            'is_close'=>Config::USER_TYPE_IS_CLOSE,//默认为开启状态
            'dt'      =>date('Y-m-d H:i:s'),
        );
        $dao_user->start_trans();
        $in_user_id=$dao_user->Insert($in_user_arr);        
        if(!$in_user_id){
            WLog::warning('in user is error '.json_encode(array('in_data'=>$in_user_arr)), array(), 'register');
            $dao_user->rollback();
            throw new CException(Errno::USER_IS_REGISTER_ERROR);
        }
        $in_agent_arr=array(
            'user_id'=>$in_user_id,
            'pid'    =>$pid,
            'dt'     =>date('Y-m-d H:i:s'),
        );
        $in_agent=$dao_user_agent->Insert($in_agent_arr);
        if(!$in_agent){
            WLog::warning('in user is error '.json_encode(array('in_data'=>$in_agent_arr)), array(), 'register');
            $dao_user->rollback();
            throw new CException(Errno::USER_IS_REGISTER_ERROR);
        }
        $dao_user->commit();
        //注册成功以后 给用户生成token=返回给app
        $token_info=Tools::encrypt_lender_token($in_user_id, $mobile);
        $data['token']    =$token_info['token'];
        return $data; 
     }
     //找回密码
     public static function forgetPwd($mobile,$new_pass){
         if (!Tools::is_mobile($mobile)) {
             WLog::warning('mobile is error '.json_encode(array('mobile'=>$mobile)), array(), 'forget_pwd');
             throw new CException(Errno::USER_IS_MOBILE_ERROR);
         }
         if (!Tools::is_valid_passwd($new_pass)) {
             WLog::warning('pass is error '.json_encode(array('pass'=>$new_pass)), array(), 'forget_pwd');
             throw new CException(Errno::USER_IS_PASS_ERROR);
         }
         //校验手机号是否是注册用户
         $dao_user=new Dao_Default_UserModel();
         $user_info=$dao_user->where(array('mobile'=>$mobile))->find();
         if(!$user_info){
             throw new CException(Errno::USER_MOBILE_NO_REG_ERROR);
         }
         if($user_info['password']==md5($new_pass)){
             throw new CException(Errno::USER_PASS_AS_OLD_ERROR);
         }
         //修改原密码
         $up_ret=$dao_user->Update(array('mobile'=>$mobile),array('password'=>md5($new_pass)));
         if($up_ret===false){
             WLog::warning('pass is error '.json_encode(array('sql'=>$dao_user->getLastSql())), array(), 'forget_pwd');
             throw new CException(Errno::USER_PASS_RESET_ERROR);
         }
         return true;
     }
     //根据uid获取用户的基本信息
     public static function getUserInfo($uid){
         $uid=intval($uid);
         if($uid==0){
             throw new CException(Errno::PARAM_INVALID);
         }
         $dao_user      = new Dao_Default_UserModel();
         $user_info=$dao_user->where(array('id'=>$uid))->find();
         if(!$user_info){
             throw new CException(Errno::USER_IS_NO_LOGIN_ERROR);
         }
         return $user;
         
     }
     //重置密码
     public static function resetPwd($uid,$old_pass,$new_pass){
         $uid=intval($uid);
         if($uid==0){
             throw new CException(Errno::USER_IS_NO_LOGIN_ERROR);
         }
         $dao_user      = new Dao_Default_UserModel();
         $user_info=$dao_user->where(array('id'=>$uid))->find();
         if(!$user_info){
             throw new CException(Errno::USER_IS_NO_LOGIN_ERROR);
         }
         if($user_info['password']!=$old_pass){
             throw new CException(Errno::USER_IS_OLD_PASS_ERROR);
         }
         if($user_info['password']==md5($new_pass)){
             throw new CException(Errno::USER_PASS_AS_OLD_ERROR);
         }
         $ret=$dao_user->Update(array('id'=>$uid),array('password'=>md5($new_pass)));
         if($ret===false){
             throw new CException(Errno::USER_PASS_RESET_ERROR);
         }
         return true;
     }
     //获取推荐人列表
     public static function getAgentList($uid,$city_name,$start_time,$end_time,$page){
         $uid =intval($uid);
         $page=intval($page) >1 ? $page : 1;
         if($uid==0){
             throw new CException(Errno::USER_IS_NO_LOGIN_ERROR);
         }
         $offset=($page-1)*Config::DATA_DEFAULT_LIMIT;
         $dao_user_agent=new Dao_Default_UserAgentModel();
         $dao_user      =new Dao_Default_UserModel();
         $sql="SELECT user.mobile,user.city,user.dt FROM user,user_agent WHERE user.id=user_agent.user_id AND user_agent.pid=$uid";
         if(!empty($start_time) || empty($end_time)){
             $sql.=" AND user.dt >='{$start_time}' and user.dt<='{$end_time}'";
         }
         if(!empty($city_name)){
             $sql.="AND city='{$city_name}'";
         }
         $result=array(
             'page'=>$page,
             'list'=>array(),
         );
         $agent_info=$dao_user->Fetch($sql,false);
         if(!$agent_info){
             return $result;
         }
         foreach ($agent_info as $key=>$val){
             $arr=array();
             $arr['mobile']   =isset($val['mobile'])?$val['mobile']:'';
             $arr['dt']       =isset($val['dt'])?$val['dt']:'';
             $arr['city_name']=isset($val['city_name'])?$val['city_name']:'';
             array_push($$result['list'], $arr);
         }
         return $result;
     }


}
