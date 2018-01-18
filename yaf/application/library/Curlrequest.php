<?php
/**
 * http请求基类
 **/
class Curlrequest
{

    /**
     * 传入地址及参数，返回调用结果
     *
     **/
    public function httpRequest($url, $method, $parameters, $multi = false)
    {
        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            die('url 参数有误');
        }

        switch ($method) {
            case 'GET':
                $url = empty($parameters) ? $url : $url . '?' . http_build_query($parameters);
                return $this->http($url, 'GET');
            default:
                $headers = array();
                if (!$multi && (is_array($parameters) || is_object($parameters))) {
                    $body = http_build_query($parameters);
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=";
                }
                return $this->http($url, $method, $body, $headers);
        }
    }


    public function http($url, $method, $postfields = null, $headers = array())
    {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, 'Mozilla/5.0 Gecko/20100101 Firefox/33.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ci, CURLOPT_HEADER, false);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        var_dump($url);
        $response = curl_exec($ci);

        curl_close($ci);
        return $response;
    }


    public static function build_http_query_multi($params)
    {
        if (!$params) {
            return '';
        }

        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--' . $boundary;
        $endMPboundary = $MPboundary . '--';
        $multipartbody = '';

        foreach ($params as $parameter => $value) {
            if (in_array($parameter, array('pic', 'image')) && $value{0} == '@') {
                $url = ltrim($value, '@');
                $content = file_get_contents($url);
                $array = explode('?', basename($url));
                $filename = $array[0];

                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"' . "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content . "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value . "\r\n";
            }
        }

        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }

    /**
     * 对象转数组
     *
     **/
    public function std_class_object_to_array($stdclassobject)
    {
        $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;

        $array = array();
        if (is_array($_array) && count($_array)>0) {
            foreach ($_array as $key => $value) {
                $value = (is_array($value) || is_object($value)) ? self::std_class_object_to_array($value) : $value;
                $array[$key] = $value;
            }
        }
        return $array;
    }

    //unicode 转utf－8
    public function decodeUnicode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ),
            $str);
    }

    //获取首字母
    public function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }

        $fchar=ord($str{0});
        if ($fchar>=ord('A')&&$fchar<=ord('z')) {
            return strtoupper($str{0});
        }
        $s1=iconv('UTF-8', 'gb2312', $str);
        $s2=iconv('gb2312', 'UTF-8', $s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if ($asc>=-20319&&$asc<=-20284) {
            return 'A';
        }
        if ($asc>=-20283&&$asc<=-19776) {
            return 'B';
        }
        if ($asc>=-19775&&$asc<=-19219) {
            return 'C';
        }
        if ($asc>=-19218&&$asc<=-18711) {
            return 'D';
        }
        if ($asc>=-18710&&$asc<=-18527) {
            return 'E';
        }
        if ($asc>=-18526&&$asc<=-18240) {
            return 'F';
        }
        if ($asc>=-18239&&$asc<=-17923) {
            return 'G';
        }
        if ($asc>=-17922&&$asc<=-17418) {
            return 'H';
        }
        if ($asc>=-17417&&$asc<=-16475) {
            return 'J';
        }
        if ($asc>=-16474&&$asc<=-16213) {
            return 'K';
        }
        if ($asc>=-16212&&$asc<=-15641) {
            return 'L';
        }
        if ($asc>=-15640&&$asc<=-15166) {
            return 'M';
        }
        if ($asc>=-15165&&$asc<=-14923) {
            return 'N';
        }
        if ($asc>=-14922&&$asc<=-14915) {
            return 'O';
        }
        if ($asc>=-14914&&$asc<=-14631) {
            return 'P';
        }
        if ($asc>=-14630&&$asc<=-14150) {
            return 'Q';
        }
        if ($asc>=-14149&&$asc<=-14091) {
            return 'R';
        }
        if ($asc>=-14090&&$asc<=-13319) {
            return 'S';
        }
        if ($asc>=-13318&&$asc<=-12839) {
            return 'T';
        }
        if ($asc>=-12838&&$asc<=-12557) {
            return 'W';
        }
        if ($asc>=-12556&&$asc<=-11848) {
            return 'X';
        }
        if ($asc>=-11847&&$asc<=-11056) {
            return 'Y';
        }
        if ($asc>=-11055&&$asc<=-10247) {
            return 'Z';
        }
        return null;
    }
}
