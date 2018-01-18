<?php
/**
 * Yindou Framework
 * (C)2015-2020 Yindou Inc. (http://www.yindou.com)
 *
 * 公用工具库
 *
 * @Package Yindou Index
 * @version $Id$
 **/

class Tools
{
    
    /**
     * @brief 对服务层的service中的参数$arrInpu数据格式化
     * @param $arrInput service中的参数
     * @param $strParamName 数组中的key
     * @return: Array 格式化后的请求数据 | 后续会增加更多格式
     **/
    public static function getArrayParams($arrInput, $strParamName)
    {
        if (false === $strParamName || !is_array($arrInput) || !isset($arrInput[$strParamName])) {
            WLog::notice('input_params_invilid', " [input] " .$strParamName);
            return false;
        }
        $param = $arrInput[$strParamName];
        if (is_array($param)) {
            return $param;
        }
        return false;
    }
    public static function pre_echo($param)
    {
        if (is_array($param)) {
            echo '<pre>'.print_r($param, true).'</pre>'."\r\n";
        } else {
            echo '<pre>'.$param.'</pre>'."\r\n";
        }
    }

    /*
     * 函数功能，实现乘100的功能，防止浮点乘100，然后转为整型 时不准确的问题
     * $in="2.01"
     */
    public static function multiply100($in)
    {
        $in = "$in"; //强制转换为字符串
        if (false === strpos($in, ".")) {
            //是整数
            return  intval($in)*100;
        } else {
            $arr = explode(".", $in);
            if ($arr[0] == "") { //处理 ".123"这种浮点数，
                $arr[0]=0;
            }
            $in = $arr[0].".".$arr[1]."00"; //先加2个0，防止位数不足
        
            $pos = strpos($in, ".");
            $ret = substr($in, 0, $pos).substr($in, $pos+1, 2);
            return intval($ret);
        }
    }
    
    public static function is_member_username($username)
    {
        if (preg_match('/^[0-9A-Za-z_.@-]{6,25}$/', $username)) {
            return strtolower($username);
        } else {
            return false;
        }
    }

    public static function is_email($str)
    {
        $pattern="/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
        if (preg_match($pattern, $str, $counts)) {
            return true;
        } else {
            return false;
        }
    }

    public static function is_mobile($str)
    {
        return preg_match('/^1\d{10}$/', $str);
    }
    
    public static function isMobileTel($str)
    {
        return preg_match('/[+0-9-]{11,17}/', $str);
    }

    //校验真名
    public static function is_realname($str)
    {
        return preg_match('/^[\x80-\xff]{2,}$/i', $str);
    }

    //校验身份证
    public static function is_id_card_num($str)
    {

        //判断是不是18位和末位是否合法
        if (!preg_match('/^\d{17}[0-9x]$/i', $str)) {
            return false;
        }

        //分解身份证信息
        $array = array();
        $array['year'] = (int)substr($str, 6, 4);
        $array['month'] = (int)substr($str, 10, 2);
        $array['day'] = (int)substr($str, 12, 2);

        if ($array['year']>date('Y')-18) {
            return false;
        } //未满18岁
        if ($array['year']<date('Y')-120) {
            return false;
        } //超过120岁
        if ($array['month']>12) {
            return false;
        } //月份错误
        if ($array['day']>31) {
            return false;
        } //日期错误

        //位数加权码
        $jiaquan = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
        $ycode = array(1,0,'x',9,8,7,6,5,4,3,2);

        //最后一位校验
        $sum = 0;
        for ($i=0;$i<17;$i++) {
            $sum += $str[$i] * $jiaquan[$i];
        }

        $Y = $sum%11;
        $lastNum = $ycode[$Y];

        if (strtolower($str[17])!=$lastNum) {
            return false;
        }

        return true;
    }

    public static function is_bank_card_num($str)
    {
        return preg_match('/^\d{16,19}$|^\d{6}[- ]\d{10,13}$|^\d{4}[- ]\d{4}[- ]\d{4}[- ]\d{4,7}$/', $str);
    }

    public static function is_id_code($id_code)
    {
        return self::is_md5($id_code);
    }

    public static function is_md5($str)
    {
        return preg_match('/^[0-9a-f]{32}$/i', $str);
    }

    public static function is_flag($flag)
    {
        return preg_match('/^[-0-9A-Za-z_]+$/', $flag);
    }

    public static function checkIsFlag($flag)
    {
        return (preg_match('/^[a-z]+/i', $flag) and preg_match('/^[a-z0-9_]+$/i', $flag));
    }

    public static function is_valid_passwd($passwd)
    {
        return preg_match('/^[0-9A-Za-z_.@-]{8,25}$/', $passwd);
    }

    public static function is_valid_mobile_code($code)
    {
        return preg_match('/^[0-9]{4}$/', $code);
    }

    /*
     * 获取ip地址
     */
    public static function getip()
    {
        if (getenv('HTTP_X_REAL_FORWARDED_FOR')&&strcasecmp(getenv('HTTP_X_REAL_FORWARDED_FOR'), 'unknown')) {
            $ip=getenv('HTTP_X_REAL_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')&&strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip=getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')&&strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip=getenv('HTTP_CLIENT_IP');
        } elseif (getenv('REMOTE_ADDR')&&strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip=getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR'])&&$_SERVER['REMOTE_ADDR']&&strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        $ip=preg_replace("/^([\d\.]+).*/", "\\1", $ip);
        return $ip;
    }
    public static function is_sn($sn)
    {
        return preg_match('/^[0-9a-zA-Z-]+$/i', $sn) ? true : false;
    }
    //分转换成元
    public static function fen2yuan($fen)
    {
        if ($fen==0) {
            return 0;
        }
        return sprintf('%.2f', ($fen/100));
    }

    //到明天还剩多少秒
    public static function leftSecond()
    {
        $tomorrow = array();
        $tomorrow['second'] = time() + 3600 * 24;
        $tomorrow['Y'] = date('Y', $tomorrow['second']);
        $tomorrow['m'] = date('m', $tomorrow['second']);
        $tomorrow['d'] = date('d', $tomorrow['second']);

        return mktime(0, 0, 0, $tomorrow['m'], $tomorrow['d'], $tomorrow['Y']) - time();
    }

    //手机号马赛克
    public static function mobile_star($mobile)
    {
        return substr($mobile, 0, 3).'****'.substr($mobile, 7);
    }

    //处理网址 如果不是以/结尾 加上/
    public static function url_tail($url)
    {
        return substr($url, -1)=='/' ? $url : $url.'/';
    }




    //获取http头
    public static function get_http_head()
    {
        return Util::isProductEnv()===true ? 'https://':'http://';
        //return (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    }
    
    public static function format_title($title=null)
    {
        $title = trim($title);
        if ($title) {
            return addslashes(self::function_strip_tags($title));
        } else {
            return false;
        }
    }
    /**
     * strip_tags函数在遇到如下情况时会出错<div fc05="" fc11="" nbw-blog="" ztag=""  js-fs2"="">
     * 所以先用正则判断一下
     * @param type $html
     */
    public static function function_strip_tags($html)
    {
        return strip_tags(preg_replace('/<(div|a|span|td|table).*?>/is', '', $html));
    }
    
    public static function object2array(&$object)
    {
        return json_decode(json_encode($object), true);
    }

    /**
     *
     * @param $url
     * @param string $method
     * @param null $postFields
     * @param null $header
     *
     * @return mixed
     * @throws Exception
     */
    public static function curl($url, $method = 'GET', $postFields = null, $header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
    
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($postFields)) {
                    if (is_array($postFields) || is_object($postFields)) {
                        if (is_object($postFields)) {
                            $postFields = Tools::object2array($postFields);
                        }
                        $postBodyString = "";
                        $postMultipart = false;
                        foreach ($postFields as $k => $v) {
                            if ("@" != substr($v, 0, 1)) { // 判断是不是文件上传
                                $postBodyString .= "$k=" . urlencode($v) . "&";
                            } else { // 文件上传用multipart/form-data，否则用www-form-urlencoded
                                $postMultipart = true;
                            }
                        }
                        unset($k, $v);
                        if ($postMultipart) {
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                        } else {
                            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, - 1));
                        }
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                    }
                }
                break;
            default:
                if (!empty($postFields) && is_array($postFields)) {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($postFields);
                }
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
    
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $response = curl_exec($ch);
         
        if (curl_errno($ch)) {
            //throw new Exception(curl_error($ch), 0);
        }
        curl_close($ch);
        return $response;
    }

    /*
     * 数字每三位加逗号的功能
     */
    public static function money_num_format($num){
        if(!is_numeric($num)){
            return false;
        }
        $num = explode('.',$num);//把整数和小数分开
        $rl = isset($num[1]) ? $num[1] : '';//小数部分的值
        $j = strlen($num[0]) % 3;//整数有多少位
        $sl = substr($num[0], 0, $j);//前面不满三位的数取出来
        $sr = substr($num[0], $j);//后面的满三位的数取出来
        $i = 0;
        $rvalue='';
        while($i <= strlen($sr)){
            $rvalue = $rvalue.','.substr($sr, $i, 3);//三位三位取出再合并，按逗号隔开
            $i = $i + 3;
        }
        $rvalue = $sl.$rvalue;
        $rvalue = substr($rvalue,0,strlen($rvalue)-1);//去掉最后一个逗号
        $rvalue = explode(',',$rvalue);//分解成数组
        if($rvalue[0]==0){
            array_shift($rvalue);//如果第一个元素为0，删除第一个元素
        }
        $rv = $rvalue[0];//前面不满三位的数
        for($i = 1; $i < count($rvalue); $i++){
            $rv = $rv.','.$rvalue[$i];
        }
        if(!empty($rl)){
            $rvalue = $rv.'.'.$rl;//小数不为空，整数和小数合并
        }else{
            $rvalue = $rv;//小数为空，只有整数
        }
        return $rvalue;
    }
    
    public static function set_curl($url, $content, $method=1)
    {
        $ch = curl_init();
        // print_r($ch);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;

    }

    /** 隐藏用户名全称
     * @param $username
     * @return string
     */
    public static function formatUsername($username) {
        if (!$username) {
            return '';
        }
        return '*'.mb_strimwidth($username, 1, 3).'**';
    }
    
    //生成app token唯一标示
    public static function encrypt_lender_token($uid, $mobile)
    {
        $result=array(
            'token'=>''
        );
        $uid=intval($uid);
        if (!$uid) {
            return $result;
        }
        if (empty($mobile)) {
            return $result;
        }
        $now_time=time();
        $token=md5($uid.$mobile.$now_time);
        $value=array(
            'uid'=>$uid,
            'set_mc_time'=>time(),  //设置缓存的时间，用来延长缓存的有效期。
        );
        $rds=Common_Cache_Redis::getInstance();
        $rds->set($token, $value,10*86400);//有效期10天
        WLog::warning('memcache lender token'.json_encode(array("token"=>$token)), array(), 'person_loan_token');
        $result['token']=$token;
        return $result;
    }
    //解析token生成uid
    public static function decry_lender_token($token)
    {
        $result=array(
            'uid'               =>0,
        );
        if (empty($token)) {
            return $result;
        }
        $rds=Common_Cache_Redis::getInstance();
        $value=$rds->get($token);
        if (!$value) {//已过期，或者已失效
            return $result;
        }
        if(is_array($value)){
            $uid=$value['uid'];
            $set_mc_time=$value['set_mc_time'];
            $temp = time()-$set_mc_time;
            $temp_days=intval($temp/86400);
            if ($temp_days>=3) {
                $mc_value = array(
                    'uid'=>$uid,
                    'set_mc_time'=>time(),
                );
                $rds->set($token, $mc_value, 10*86400);//有效期10天
            }
            $result['uid']=$uid;
            return $result;
        }else {//这种情况，缓存的值无效，那先报个错吧
            WLog::warning('app_person_login_state_error'.json_encode(array("token"=>$token)), array(), 'person_loan_token');
            return $result;
        }
    }
    //删除用户相应token
    public static function del_lender_token($token)
    {
        if (empty($token)) {
            return false;
        }
        $rds=Common_Cache_Redis::getInstance();
        $uid=$rds->del($token);
        return $uid;
    }
    
    //计算 发送验证码 cache 时间
    public static function deco_sms_cache_time()
    {
        $time= time();
        $end_time=strtotime(date('Y-m-d 23:59:59'));
        $cache_time=$end_time-$time;
        return $cache_time;
    }
    
    //生成 photo_title 中的 filename
    
    public static function get_photo_url($type, $filename)
    {
        if (empty($type) || empty($filename)) {
            return null;
        }
        return Config::UPLOAD_URL.'photo_uploader/'.$type.'/'.$filename;
    }
   

}
