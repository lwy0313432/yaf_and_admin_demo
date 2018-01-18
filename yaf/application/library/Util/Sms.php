<?php
/**
 * @package yindou
 * @brief  发送短信 处理
 * @author weixiaotong <weixt@yindou.com>
 * @date 2017-12-20
 * @encoding UTF-8
 * @copyright (c) yindou
 */
class Util_Sms
{

    
    private static $_count=1;
    
    
    
    //获取  已发短信信息
    public static function get_sms_result_info($mobile,$type,$sms_code,$lender_id=0)
    {
        if (!in_array($type, Config::get_sms_type())) {
            throw new CException(Errno::USER_SEND_SMS_TYPE_ERROR);
        }
        if ($lender_id==0) {
            if (!Tools::is_mobile($mobile)) {
                throw new CException(Errno::USER_IS_MOBILE_ERROR);
            }
            $where_arr=array('mobile'=>$mobile,'type'=>$type,'code'=>$sms_code,'is_expired'=>0,'left_times'=>array('gt',0));
        } else {
            $where_arr=array('lender_id'=>$lender_id,'type'=>$type,'code'=>$sms_code,'is_expired'=>0,'left_times'=>array('gt',0));
        }
        $dao_mobile_code=new Dao_Default_MobileCodeModel();
        $result=$dao_mobile_code->where($where_arr)->order('id desc')->find();
        if ($result===false) {
            throw new CException(Errno::USER_GET_SMS_INFO_ERROR);
        }
        if (empty($result)) {
           /* $up_sql="update `mobile_code` set left_times=left_times-1 where mobile='".$mobile."' order by id desc limit 1";
            $up_left_times=$dao_mobile_code->Fetch($up_sql);*/
            throw new CException(Errno::USER_GET_SMS_INFO_ERROR);
        }
        if (isset($result['time_send']) && round(($result['time_send']-time())/60)>10) {
            throw new CException(Errno::USER_GET_SMS_CODE_EXPIRE_ERROR);
        }
        self::set_is_expired($result);
        return true;
    }
    
    public static function set_is_expired($data)
    {
        $id=isset($data['id'])?intval($data['id']):0;
        if (!$id) {
            return false;
        }
        $dao_mobile_code=new Dao_Default_MobileCodeModel();
        $up_arr=array(
                'is_expired'=>1,
        );
        $ret=$dao_mobile_code->Update(array('id'=>$id), $up_arr);
        if ($ret===false) {
            WLog::warning('up is_expired is error'.json_encode(array('id'=>$id)), array(), 'send_sms');
        }
        return $ret;
    }
    
    public static function set_left_times($id)
    {
        $id=intval($id);
        if (!$id) {
            return false;
        }
        $up_arr=array(
                'left_times'=>left_times-1,
        );
        $dao_mobile_code=new Dao_Default_MobileCodeModel();
        $ret=$dao_mobile_code->Update(array('id'=>$id), $up_arr);
        if ($ret===false) {
            WLog::warning('up left_times is error'.json_encode(array('id'=>$id)), array(), 'send_sms');
        }
        return $ret;
    }
    
    //发送验证码所有 send + cache 验证
    
    public static function send_sms($mobile,$type,$lender_id=0,$voice_or_txt='')
    {

        if ($voice_or_txt!=Config::MSG_TEXT && $voice_or_txt!=Config::MSG_VOICE) {
            //不符合条件，那还是走短信验证码吧。
            $voice_or_txt = Config::MSG_TEXT;
        }
        if (!Tools::is_mobile($mobile)) {
            throw new CException(Errno::USER_IS_MOBILE_ERROR);
        }
        
        if (!in_array($type, Config::get_sms_type())) {
            throw new CException(Errno::USER_SEND_SMS_TYPE_ERROR);
        }
        
        $mobile_send_count=self::get_codeum_today($mobile, $type);
        if ($mobile_send_count >= Config::SMS_IS_MAX_TODAY) {
             throw new CException(Errno::USER_SEND_SMS_MAX_ERROR);
         }
        self::$_count=$mobile_send_count===false?1:$mobile_send_count+1;

        
        //将原来发的验证码都设置为过期。
        $ret=self::setMobileCodeExpired($mobile, $type);
        if ($ret===false) {
            WLog::warning('reset sms_code is error'.json_encode(array('result'=>$ret)), array(), 'send_sms');
        }
        //在不超过过期时间，不再生成新的验证码
        $sms_code='';
        $mobile_code_info=self::get_mobile_code($mobile, $type);
        if ($mobile_code_info) {
            if ((time()-$mobile_code_info['time_send'])<600 && $mobile_code_info['left_times']>0) {
                $sms_code=$mobile_code_info['code'];
            }
        }
        //获取生成的内容及code
        $sms_info=self::getSmsContent($type, $sms_code);
        if (!isset($sms_info['code']) || !isset($sms_info['content'])) {
            WLog::warning('generate sms code is error'.json_encode(array('mobile'=>$mobile)), array(), 'send_sms');
           throw new CException(Errno::USER_SEND_SMS_IS_ERROR);
        }
        $mobile_code_id = self::addMobileCode($type, $mobile, $sms_info['code'], $lender_id, $sms_info['left_times']);//写入到mobile_code表
        if (!$mobile_code_id) {
            WLog::warning(' mobile_code_id'.json_encode(array('mobile'=>$mobile,'type'=>$type)), array(), 'send_sms');
           throw new CException(Errno::USER_SEND_SMS_IS_ERROR);
        }
        $sms_id = self::addSms($sms_info['send_type'], $mobile, $sms_info['content'], 1, $lender_id);//插入表sms表。
        if (!$sms_id) {
            WLog::warning(' get_sms_id'.json_encode(array('mobile'=>$mobile,'type'=>$type)), array(), 'send_sms');
            throw new CException(Errno::USER_SEND_SMS_IS_ERROR);
        }
        $send_status = SendMsg::do_send($mobile, $sms_info['content'], $sms_info['code'], $voice_or_txt);//发送验证码
        if (!$send_status['status']) {//根据发送结果，更新sms表。
            WLog::warning(' send_sms_code_fail'.json_encode(array('mobile'=>$mobile,'type'=>$type,'send_status'=>$send_status)), array(), 'send_sms');
            self::updateSms(Config::SMS_STATUS_IS_FAILD, $sms_id, $send_status['send_channel']);
            throw new CException(Errno::USER_SEND_SMS_IS_ERROR);
        } else {
            self::updateSms(Config::SMS_STATUS_IS_SUCCESS, $sms_id, $send_status['send_channel']);
            //发送成功后，给cache加值
            $set=self::setMoileCodeNumToday($mobile, $type);
            if ($set===false) {
                WLog::warning('set_cache_is_fail'.json_encode(array('mobile'=>$mobile,'type'=>$type)), array(), 'send_sms');
            }
        }
        return true;
    }
    
    /**
     * 获取发送次数
     * @param unknown $mobile
     * @param unknown $type
     * @return unknown
     */
    private static function get_codeum_today($mobile, $type)
    {
        $key=Config::SMS_IS_REDIS_CONFIG_KEY.'_'.$mobile.'_'.$type;
        $rds=Common_Cache_Redis::getInstance();
        $count=$rds->get($key);
        return $count;
    }
    
    /**
     * 在缓存中设置手机号发送次数
     * @param unknown $mobile$key,$value,$expire=0
     * @param unknown $type
     */
    private static function setMoileCodeNumToday($mobile, $type)
    {
        $cache_time=Tools::deco_sms_cache_time();
        $key=Config::SMS_IS_REDIS_CONFIG_KEY.'_'.$mobile.'_'.$type;
        $rds=Common_Cache_Redis::getInstance();
        $set=$rds->set($key, self::$_count, $cache_time);
        return $set;
    }
    
    //将该手机号下的，该种类的验证码都失效
    //
    private static function setMobileCodeExpired($mobile, $type)
    {
        $dao_mobile_code=new Dao_Default_MobileCodeModel();
        $arr_conds = array(
                'mobile'=>$mobile,
                'type'=>$type,
                'is_expired'=>Config::SMS_NO_EXPIRED_TYPE,
        );
        $ret=$dao_mobile_code->Update($arr_conds, array('is_expired'=>Config::SMS_EXPIRED_TYPE));
        return $ret;
    }
    
    //根据type 生成短信发送内容
    private static function getSmsContent($type, $code='')
    {
        $content='';
        if (empty($code)) {
            $code = substr(microtime(), 2, 6);
        }
        $send_type= '';
        $content="验证码为：$code,有效期：".Config::MOBILE_CODE_EXPIRED_TIME."分钟。如有疑问请致电：".Config::SERVICE_MOBILE;
        $send_type = $type;
        $left_times = self::$_count;
        if ($type==Config::SMS_REGISTER_TYPE) {
            $content="您的注册验证码为：$code,有效期：".Config::MOBILE_CODE_EXPIRED_TIME."分钟。如有疑问请致电：".Config::SERVICE_MOBILE;
            $left_times = self::$_count;
        } else {
            $content="验证码为：$code,有效期：".Config::MOBILE_CODE_EXPIRED_TIME."分钟。如有疑问请致电：".Config::SERVICE_MOBILE;
            $left_times = self::$_count;
        }

        return array('code'=>$code,'content'=>$content,'send_type'=>$send_type,'left_times'=>$left_times);
    }
    //插入到mobile_code 表中
    private static function addMobileCode($type, $mobile, $code, $lender_id=0, $left_times='')
    {
        $dao_mobile_code=new Dao_Default_MobileCodeModel();
        $ip = Tools::getip();
        $type=addslashes($type);
        $arr_input=array(
                'lender_id'=>intval($lender_id),
                'type'=>$type,
                'mobile'=>$mobile,
                'code'=>$code,
                'time_send'=>time(),
                'is_expired'=>Config::SMS_NO_EXPIRED_TYPE,
                'left_times'=>Config::SMS_DEFAULT_LEFT_TIMES,
                'ip'=>$ip,
                'dt'=>date('Y-m-d H:i:s'),
        );
        $insert_id = $dao_mobile_code->Insert($arr_input);//写入到mobile_code表。
        if (!$insert_id) {
            return false;
        }
        return $insert_id;
    }
    //插入到sms 表中
    private static function addSms($send_type, $mobile, $content, $at_once=1, $lender_id=0)
    {
        $dao_sms=new Dao_Default_SmsModel();
        $ip = Tools::getip();
        $arr_input_sms=array(
                'lender_id'=>intval($lender_id),
                'mobile'=>$mobile,
                'send_type'=>$send_type,
                'content'  =>$content,
                'send_time'=>date("Y-m-d H:i:s"),
                'dt'       =>date("Y-m-d H:i:s"),
                'at_once'=>$at_once,
                'status'=>0,
                'ip'=>$ip,
                'msg_id'=>0,//先给个默认值
        );

        $sms_id = $dao_sms->Insert($arr_input_sms);//插入表sms表。
        if (!$sms_id) {
            WLog::warning('insert into sms error'.json_encode(array('mobile'=>$mobile,'send_type'=>$send_type)), array(), 'send_sms');
            return false;
        }
        return $sms_id;
    }
    
    //根据 发送状态 更新 mobile_code 状态
    private static function updateSms($status, $sms_id, $send_channel=0)
    {
        $dao_sms=new Dao_Default_SmsModel();
        $arr_in = array(
                'status'=>$status,
                'send_channel'=>$send_channel,
                'send_time'=>date("Y-m-d H:i:s"),
        );
        $update_ret = $dao_sms->Update(array('id'=>$sms_id), $arr_in);
        if (!$update_ret) {
            WLog::warning('update sms db error'.json_encode(array('status'=>$status,'sms_id'=>$sms_id)), array(), 'send_sms');
            return false;
        }
    }
    
    public static function get_mobile_code($mobile, $type)
    {
        if (empty($mobile) || empty($type)) {
            return false;
        }
        $dao_mobile_code=new Dao_Default_MobileCodeModel();
        $where_arr=array(
                'mobile'=>$mobile,
                'type'=>$type,
        );
        $result=$dao_mobile_code->where($where_arr)->order('id desc')->find();
        return $result;
    }
    
}
