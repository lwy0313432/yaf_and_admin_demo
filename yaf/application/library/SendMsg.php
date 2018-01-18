<?php

/**
 * @package yindou
 * @brief  短信发送基类
 * @author weixiaotong <weixt@yindou.com>
 * @date 2017-03-1
 * @encoding UTF-8
 * @copyright (c) yindou
 */
class SendMsg
{
    /*
     * 短信发送
     */
    public static function do_send($mobile, $content, $mobile_code='', $voice_or_text='text')
    {
        //如果是语音验证码，直接用创蓝的发送，
        
        if ($voice_or_text==Config::MSG_VOICE) {
            //语音验证码，必须传入验证码这串数字。
            if (!$mobile_code) {
                WLog::warning('clzg_send_audio_err'.json_encode(array('mobile'=>$mobile,'content'=>$content,'mobile_code'=>$mobile_code,'voice_or_text'=>$voice_or_text)), array(), 'send_sms');
                return array('status'=>false,'send_channel'=>Config::SMS_IS_CLZG_VOICE);
            }
            $status = self::send_msg_voice_by_clzg($mobile, $mobile_code);
            return array('status'=>$status,'send_channel'=>Config::SMS_IS_CLZG_VOICE);
        }
        //先用创蓝中国的发送，如果发送失败，就切换到亿美软通，再次发送。
        $clzg=self::clzg_send_msg($mobile, $content);
         
        if ($clzg) {
            return array('status'=>$clzg,'send_channel'=>Config::SMS_IS_CLZG);
        } else {
            WLog::warning('clzg_send_err'.json_encode(array('mobile'=>$mobile,'content'=>$content,'mobile_code'=>$mobile_code,'voice_or_text'=>$voice_or_text)), array(), 'send_sms');
            $ymrt_ret = self::ymrt_send_msg($mobile, $content); //短信签名，亿美软通已经帮我们加了
            return array('status'=>$ymrt_ret,'send_channel'=>Config::SMS_IS_YMRT);
        }
    }
    //营销短信发送--优先使用空间畅想
    //返回两个参数，一个是否成功，一个发送渠道标识
    public static function do_market_send($mobile, $content, $send_time='')
    {
        $kjcx_sms=self::kjcx_send_marketing_msg($mobile, $content, $send_time);
        if($kjcx_sms){
            return array('status'=>$kjcx_sms,'send_channel'=>Config::SMS_IS_KJCX_MART);die;
        }else{
            $clzg_sms=self::clzg_send_marketing_msg($mobile, $content, $send_time);
            return array('status'=>$clzg_sms,'send_channel'=>Config::SMS_IS_CLZG_MART);die;
        }
    }
    private static function send_msg_voice_by_clzg($mobile, $mobile_code)
    {
        $ACCOUNT_NAME = Yaf_Registry::get('config')->CLZG_VOICE_MSG_ACCOUNT;
        $ACCOUNT_PWD  = Yaf_Registry::get('config')->CLZG_VOICE_MSG_PASSWD;
        $KEY  = Yaf_Registry::get('config')->CLZG_VOICE_KEY;
        
        //$ACCOUNT_NAME = "YC8821371";
        //$ACCOUNT_PWD = 'NB8muQnz526ce5';
        //$KEY = 'db595a0739dccd1e82a17c11308c8311';
        if (!$ACCOUNT_NAME || !$ACCOUNT_PWD ||!$KEY) {
            return true;
        }
        
        $url = 'http://audio.253.com/voice';
        $timestamp =date("YmdHis");
        $data = array();
        $data['organization'] = $ACCOUNT_NAME; //必填
        $data['phonenum'] = $mobile; //必填
        $data['timestamp'] =  $timestamp; //选填
        $data['content'] = md5($KEY.$data['phonenum'].$ACCOUNT_PWD.$timestamp); //必填
        $data['vfcode'] = $mobile_code; //必填
        $data['shownum'] = '95213176';  //审核通过的来电显示号码  //必填

        $bodyArr['voiceinfo'] = $data;
        $body = urlencode(json_encode($bodyArr));
 
        $post_data = 'method=vcfplay&voiceinfo='.$body;
    // 提交请求
        $con = curl_init();
        curl_setopt($con, CURLOPT_URL, $url);
        curl_setopt($con, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($con, CURLOPT_HEADER, 0);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($con, CURLOPT_POST, 1);
        curl_setopt($con, CURLOPT_POSTFIELDS, $post_data);
        
        $result = curl_exec($con);
        curl_close($con);

        $res=preg_split("/[,\r\n]/", $result);
        if (isset($res[1]) && $res[1]==0) {
            return true;
        } else {
            WLog::warning('clzg_send_voice_err'.json_encode(array('ret' => $result, 'mobile' => $mobile, 'mobile_code' => $mobile_code)), array(), 'send_sms');
            return false;
        }
    }
     
    
    private static function clzg_send_marketing_msg($mobile, $content, $send_time='')
    {
        $clzg_market_account = Yaf_Registry::get('config')->MARKETING_SMS_GLZG_ACCOUNT;
        $clzg_market_passwd  = Yaf_Registry::get('config')->MARKETING_SMS_CLZG_PASS;
        
        $post_data = array();
        $post_data['un'] = $clzg_market_account;//账号
        $post_data['pw'] = $clzg_market_passwd;//密码
        $post_data['msg']= $content.'回T退订';
        $post_data['phone'] =$mobile;//手机
        $post_data['rd']=1;
        
        $url='http://sms.253.com/msg/send';
        $res=Tools::curl($url, 'POST', $post_data);
        $result=preg_split("/[,\r\n]/", $res);
        if (isset($result[1]) && $result[1]==0) {
            return true;
        } else {
            WLog::warning('clzg_send_market_err'.json_encode(array('ret' => $result, 'mobile' => $mobile, 'content' => $content)), array(), 'send_sms');
            return false;
        }
    }
    
    /*
     * 亿美软通发送即时短信接口
    */
    private static function ymrt_send_msg($mobile, $content, $send_time='')
    {
        /*
         * 即时短信：http://sdk999ws.eucp.b2m.cn:8080/sdkproxy/sendsms.action?
        * 定时短信：http://sdk999ws.eucp.b2m.cn:8080/sdkproxy/sendtimesms.action
        */
    
        $ymrt_account = Yaf_Registry::get('config')->SMS_YMRT_ACCOUNT;
        $ymrt_passwd  = Yaf_Registry::get('config')->SMS_YMRT_PASS;
        //$ymrt_passwd='';
        if (!$ymrt_account or !$ymrt_passwd) {
            // 注：在本地测试时，将两个常量定义为空，返回true，但是实际上不发送短信
            return true;
        }
        $param['cdkey']    = $ymrt_account; //用户序列号。;
        $param['password'] = $ymrt_passwd; //用户密码
        $param['seqid'] = ''; //长整型值企业内部必须保持唯一，获取状态报告使用
        $param['phone'] = $mobile; //手机号码（最多1000个），多个用英文逗号(,)隔开。
        $param['message'] = $content; //短信内容（UTF-8编码）（最多500个汉字或1000个纯英文）。
        $param['addserial'] = ''; //附加号（最长10位，可置空）。
        if ($send_time && strtotime($send_time)&& strtotime($send_time) > time()) {//发送时间必须大于当前时间，才发定时短信
            $url="http://sdk999ws.eucp.b2m.cn:8080/sdkproxy/sendtimesms.action";
            $param['sendtime'] = date("YmdHis", strtotime($send_time));
            //预定发送时间(格式为：yyyymmddhhnnss) 注：预定发送时间需大于当前标准机器时间，建议在10分钟以上。
        } else {
            $url="http://sdk999ws.eucp.b2m.cn:8080/sdkproxy/sendsms.action";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                // 关闭输出，返回字符串
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, false);                        // 返回header
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);    //注意，毫秒超时设置这个
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
        $ret = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        $matches=array();
        preg_match("/.*<error>(.*)<\/error>/i", $ret, $matches);
        if (count($matches) >=2) {
            $code = intval($matches[1]);
            if ($code==0) {
                return true;
            } else {
                WLog::warning('YMRT SEND SMS IS ERROR'.json_encode(array('mobile'=>$mobile,'code'=>$code)), array(), 'send_sms');
                return false;
            }
        } else {
            WLog::warning('YMRT SEND SMS IS ERROR'.json_encode(array('mobile'=>$mobile,'code'=>$code)), array(), 'send_sms');            
            return false;
        }
    }
    
    /**
     * 美联软通,发短信
     */
    public static function do_send_mlrt($mobile, $content)
    {
        $mlrt_account = Yaf_Registry::get('config')->SMS_MLRT_ACCOUNT;
        $mlrt_passwd = Yaf_Registry::get('config')->SMS_MLRT_PASS;
        $mlrt_api_key = Yaf_Registry::get('config')->SMS_MLRT_API_KEY;
        if (!$mlrt_account or !$mlrt_passwd or !$mlrt_api_key) {
            // 注：在本地测试时，将两个常量定义为空，返回true，但是实际上不发送短信
            return true;
        }
        $param['username'] = $mlrt_account; //'bjdfcy';
        $param['password'] = $mlrt_passwd; //'YinDou2910Zhaiquan01';
        $param['apikey']   = $mlrt_api_key;
        $param['mobile']   = $mobile;
        $param['content']  = iconv('utf-8', 'gbk', $content);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://m.5c.com.cn/api/send/');
        curl_setopt($ch, CURLOPT_PORT, 80);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                // 关闭输出，返回字符串
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, false);                        // 返回header
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);    //注意，毫秒超时设置这个
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
        $ret = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if (strtolower(substr($ret, 0, 7)) == 'success') {
            $arr_temp = explode(":", $ret);
            if (count($arr_temp) >= 2) {
                return $arr_temp[1];
            } else {
                return Config::SMS_IS_MLRT;
            }
        } else {
            WLog::warning('MLRT SEND SMS IS ERROR'.json_encode(array('ret' => $ret, 'mobile' => $mobile, 'content' => $content, 'curl_error' => $error)), array(), 'send_sms');
            return false;
        }
    }

    /**
     * 美联软通，绑定ip地址，指定，只有绑定的ip地址才能发短信。其他地址无法发送。
     */
    public static function mlrt_bind_ip($ip)
    {
        $mlrt_account = Yaf_Registry::get('config')->SMS_MLRT_ACCOUNT;
        $mlrt_passwd = Yaf_Registry::get('config')->SMS_MLRT_PASS;
        $mlrt_api_key = Yaf_Registry::get('config')->SMS_MLRT_API_KEY;
        if (!$mlrt_account or !$mlrt_passwd or !$mlrt_api_key) {
            // 注：在本地测试时，将两个常量定义为空，返回true，但是实际上不发送短信
            return true;
        }

        $param['username'] = $mlrt_account; //'bjdfcy';
        $param['password'] = $mlrt_passwd; //'YinDou2910Zhaiquan01';
        $param['apikey'] = $mlrt_api_key;
        $param['ip'] = $ip;
        $param['action'] = 0;    //0 为绑定ip，1为查询，2为清空

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://m.5c.com.cn/api/bind/index.php');
        curl_setopt($ch, CURLOPT_PORT, 80);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                // 关闭输出，返回字符串
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, false);                        // 返回header
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        $ret = curl_exec($ch);
        curl_close($ch);
        
        if (strtolower(substr($ret, 0, 7)) == 'success') {
            return true;
        } else {
            return false;
        }
    }
    
    /*
    * 营销短信的配置
    * 亿美软通发送即时短信接口
    *
    */
    private function ymrt_send_marketing_msg($mobile, $content, $send_time='')
    {
        $market_sms_ymrt_account = Yaf_Registry::get('config')->MARKETING_SMS_YMRT_ACCOUNT;
        $market_sms_ymrt_pass = Yaf_Registry::get('config')->MARKETING_SMS_YMRT_PASS;
        if (!$market_sms_ymrt_account or !$market_sms_ymrt_pass) {
            return true;
        }
        $param['cdkey'] = $market_sms_ymrt_account; //用户序列号。;
        $param['password'] = $market_sms_ymrt_pass; //用户密码
        $param['seqid'] = ''; //长整型值企业内部必须保持唯一，获取状态报告使用
        $param['phone'] = $mobile; //手机号码（最多1000个），多个用英文逗号(,)隔开。
        $param['message'] = $content.'回复td退订'; //短信内容（UTF-8编码）（最多500个汉字或1000个纯英文）。
        $param['addserial'] = ''; //附加号（最长10位，可置空）。
        if ($send_time && strtotime($send_time)&& strtotime($send_time) > time()) {//发送时间必须大于当前时间，才发定时短信
            $url="http://sdktaows.eucp.b2m.cn:8080/sdkproxy/sendtimesms.action";
            $param['sendtime'] = date("YmdHis", strtotime($send_time));
            //预定发送时间(格式为：yyyymmddhhnnss) 注：预定发送时间需大于当前标准机器时间，建议在10分钟以上。
        } else {
            $url="http://sdktaows.eucp.b2m.cn:8080/sdkproxy/sendsms.action";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                // 关闭输出，返回字符串
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, false);                        // 返回header
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);    //注意，毫秒超时设置这个
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
        $ret = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        preg_match("/.*<error>(.*)<\/error>/i", $ret, $matches);
        if (count($matches) >=2) {
            $code = intval($matches[1]);
    
            if ($code==0) {
                return Config::SMS_IS_YMRT_MART;
            } else {
                WLog::warning('YMRT SEND SMS IS ERROR'.json_encode(array('ret' => $ret, 'mobile' => $mobile, 'content' => $content, 'code' => $code)), array(), 'send_sms');
                return false;
            }
        } else {
            WLog::warning('YMRT SEND SMS IS ERROR'.json_encode(array('ret' => $ret, 'mobile' => $mobile, 'content' => $content, 'curl_error' => $error)), array(), 'send_sms');
            return false;
        }
    }
    
    /*
     * 创蓝中国发送短信接口
    */
    private static function clzg_send_msg($mobile, $content, $needstatus='true')
    {
        $sms_clzg_account = Yaf_Registry::get('config')->SMS_CLZG_ACCOUNT;
        $sms_clzg_pass    = Yaf_Registry::get('config')->SMS_CLZG_PASS;
        if (!$sms_clzg_account or !$sms_clzg_pass) {
            return true;
        }
        //创蓝接口参数
        $postArr = array(
                'account' => $sms_clzg_account,
                'pswd' => $sms_clzg_pass,
                'msg' => $content,
                'mobile' => $mobile,
                'needstatus' => $needstatus
        );
        $url = 'https://sapi.253.com/msg/HttpBatchSendSM';
        $postFields = http_build_query($postArr);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
         
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $result = curl_exec($ch);
        curl_close($ch);
        $ret = preg_split("/[,\r\n]/", $result);
        if (isset($ret[1]) && $ret[1]==0) {
            return true;
        } else {
            WLog::warning('CLZG SEND SMS IS ERROR'.json_encode(array('ret' => $result, 'mobile' => $mobile, 'content' => $content)), array(), 'send_sms');
            return false;
        }
    }

    /**
     * 美联软通，查询发送状态报告，
     */
    public static function mlrt_query_send_report()
    {
        $mlrt_account = Yaf_Registry::get('config')->SMS_MLRT_ACCOUNT;
        $mlrt_passwd = Yaf_Registry::get('config')->SMS_MLRT_PASS;
        $mlrt_api_key = Yaf_Registry::get('config')->SMS_MLRT_API_KEY;
        if (!$mlrt_account or !$mlrt_passwd or !$mlrt_api_key) {
            // 注：在本地测试时，将两个常量定义为空，返回true，但是实际上不发送短信
            return true;
        }

        $param['username'] = $mlrt_account; //'bjdfcy';
        $param['password'] = $mlrt_passwd; //'YinDou2910Zhaiquan01';
        $param['apikey']   = $mlrt_api_key;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://m.5c.com.cn/api/recv');
        curl_setopt($ch, CURLOPT_PORT, 80);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                // 关闭输出，返回字符串
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, false);                        // 返回header
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        $ret = curl_exec($ch);
        curl_close($ch);
        
        return $ret;
    }
    
    /*
     * 空间畅想（营销类）短信发送
     * */
    private function kjcx_send_marketing_msg($mobile, $content, $send_time=''){
        $kjcx_name = Yaf_Registry::get('config')->MARKETING_SMS_KJCX_ACCOUNT;
        $kjcx_pass = Yaf_Registry::get('config')->MARKETING_SMS_KJCX_PASS;
        if (!$kjcx_name or !$kjcx_pass) {
            // 注：在本地测试时，将两个常量定义为空，返回true，但是实际上不发送短信
            return true;
        }
        $seed    = date('YmdHis');
        $key     = md5(md5($kjcx_pass).$seed);
        $content = '【银豆网】'.$content.'~回N退订';
        $str_r   = '';
        if(!empty($send_time)){
            $str_r = "&delay=".date('YmdHis',strtotime($send_time));
        }
        $url = "http://api.cxton.com:8080/eums/utf8/send_strong.do?name=".$kjcx_name."&seed=".$seed."&key=".$key."&dest=".$mobile.$str_r."&content=".$content;
        @$ret= file_get_contents($url);
        $ret = explode(':',$ret);
        if ($ret[0] == 'success'){
            return true;
        }
        WLog::warning('CLZG SEND SMS IS ERROR'.json_encode(array('ret' => $ret, 'mobile' => $mobile, 'content' => $content)), array(), 'send_sms');
        return false;
    }   
}
