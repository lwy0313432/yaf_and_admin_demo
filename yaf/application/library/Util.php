<?php
/**
 * @describe:
 * @author: Jerry Yang(hy0kle@gmail.com)
 * */
class Util
{
    const charset = 'abcdefghzkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ3456789';   //随机因子

    public static function isBinary($str)
    {
        $blk = substr($str, 0, 512);

        return (substr_count($blk, "\x00") > 0);
    }

    /**
     * @brief escape 防 xss 攻击
     *
     * @param: $str
     *
     * @return: string
     */
    public static function escape($str)
    {
        return Util::isBinary($str) ? addslashes($str) : htmlspecialchars(trim($str), ENT_QUOTES);
    }

    /** 检查变量名和函数名的合法性 */
    public static function checkVariableNameValidate($var_name)
    {
        $pattern = '/^[_a-zA-Z][_a-zA-Z0-9]*$/';
        return preg_match($pattern, $var_name) ? 1 : 0;
    }

    /**
     * 创建 uniqid
     * @return string
     * */
    public static function createUniqueId()
    {
        return uniqid(mt_rand()) . mt_rand() . microtime(true) . self::createRandomStr(16);
    }

    /**
     * php 实现 linux 系统命令的 mkdir -p 功能
     * 默认创建的目录权限是 drwxr-xr-x
     * 目录创建成功返回 true
     * 创建失败返回 false
     * */
    public static function mkdirs($dir, $mode = 0755)
    {
        if (! is_dir($dir)) {
            if (! self::mkdirs(dirname($dir), $mode)) {
                return false;
            }
            if (! mkdir($dir, $mode)) {
                return false;
            }
        }
        return true;
    }

    public static function createRandomStr($length)
    {
        assert($length >= 2);
        $str = self::charset;

        $r_str = '';
        for ($i = 0; $i < $length; $i++) {
            $r_str .= $str[mt_rand() % strlen($str)];
        }

        return $r_str;
    }

    /*
     * 手机号码的正则验证
     */
    public static function isValidMobile($mobile)
    {
        // 输入的手机号码格式的验证
        if (empty($mobile) || strlen(trim($mobile)) != 11) {
            return false;
        }
        if (!preg_match("/1\d{10}/", $mobile)) {
            return false;
        }
        return true;
    }

    public static function passwordEncrypt($password, $salt)
    {
        $md51 = md5($password . $salt);
        $md52 = md5($salt);
        $result = substr($md52, 24, 8);
        $result .= substr($md51, 0, 24);
        return md5($result);
    }

    /**
     * 签名生成算法
     * @param  array  $params API调用的请求参数集合的关联数组，不包含sign参数
     * @param  string $secret 签名的密钥即获取access token时返回的session secret
     * @return string 返回参数签名值
     */
    public static function baiduSignature($params, $secret)
    {
        $str = '';  //待签名字符串
        //先将参数以其参数名的字典序升序进行排序
        ksort($params);
        //遍历排序后的参数数组中的每一个key/value对
        foreach ($params as $k => $v) {
            //为key/value对生成一个key=value格式的字符串，并拼接到待签名字符串后面
            $str .= "$k=$v";
        }
        //将签名密钥拼接到签名字符串最后面
        $str .= $secret;
        //通过md5算法为签名字符串生成一个md5签名，该签名就是我们要追加的sign参数值
        return md5($str);
    }

    public static function generateMobileCaptcha($length = 6)
    {
        assert($length >= 4);

        $start = str_pad('1', $length, '0');
        $end   = str_pad('9', $length, '9');
        //echo $start, ' - ', $end, PHP_EOL;
        return mt_rand((int)$start, (int)$end);
    }

    /** 判断当前环境是不是线上生产环境 */
    public static function isProductEnv()
    {
        $env = ini_get('yaf.environ');
        //echo $env, PHP_EOL;exit;
        if ('product' == $env) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 获取pay域名
     */
    public static function getPayDomain()
    {
        $config = Yaf_Application::app()->getConfig();
        $bank_conf = $config->get('BANK');
        if (isset($bank_conf['PAY_DOMAIN'])) {
            return $bank_conf['PAY_DOMAIN'];
        } else {
            return "https://pay.yindou.com/";
        }
    }

    public static function getUri()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    public static function xml2Array($xml)
    {
        $xmlObj = simplexml_load_string($xml);
        return json_decode(json_encode($xmlObj), true);
    }

    /**
     * 8~18位数字和字母的组合
     * */
    public static function isHighStrengthPwd($password)
    {
        // 长度不够
        if (strlen($password) < 8) {
            return 0;
        }
        // 纯数字或纯字母
        if (ctype_alpha($password) || ctype_digit($password)) {
            return 0;
        }

        return 1;
    }

    /*
     * 判断用户浏览器
     * */
    public static function useragent(){
        if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            return 'weixin';
        }elseif(isset($_SERVER['HTTP_USER_AGENT']) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'yindou') !== false){
            return 'yindou';
        }else{
            return 'normal';
        }
    }

    public static function is_mis_valid_passwd($password)
    {
        if (!is_string($password)) {
            return false;
        }    
        if (strlen($password) <8) {
            return false;
        }    
        if (!preg_match('/[0-9]+/', $password)) {//不包含数字
            return false;
        }    
        if (!preg_match('/[a-z]+/', $password)) {//不包含大写字母
            return false;
        }    
        if (!preg_match('/[A-Z]+/', $password)) {//不包含大写字母
            return false;
        }   
        if(!preg_match('/[`\~\!\@\#\$\%\^\&\*\(\)\_\+\=\_\[\]\{\}\'\"\:\;\,\.\/<>\|\?]+/',$password)){//必须包含一个特殊字符
            return false;
        }        
        return true;
    }   
}
/* vi:set ts=4 sw=4 et fdm=marker: */
