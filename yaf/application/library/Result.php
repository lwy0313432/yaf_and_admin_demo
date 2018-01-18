<?php

/**
 * @package Library
 * @brief  返回处理结果
 * @author weixiaotong <weixt@yindou.com>
 * @date 2016-03-17
 * @encoding UTF-8
 * @copyright (c) yindou
 */

class Result
{
    public static function getErrCode($intErrno)
    {
        if (isset(Errno::$codes[$intErrno])) {
            return Errno::$codes[$intErrno];
        }
        return 'the errno is not defined';
    }
    
    public static function getErrUsercode($intErrno)
    {
        if (isset(Errno::$usercodes[$intErrno])) {
            return Errno::$usercodes[$intErrno];
        }
        return '未知';
    }
    
    public static function sucess($mixData = null, $msg='')
    {
        /*
        $arrRet = array(
            'errno'     => Errno::SYSTEM_STOP,
            'code'      => self::getErrCode( Errno::SYSTEM_STOP ),
            'usercode'  => self::getErrUsercode( Errno::SYSTEM_STOP ),
            'data'      => 'https://www.yindou.com/halt_wap.php',
        );
        return $arrRet;
        */
        $arrRet = array(
            'errno'     => Errno::ERR_SUCESS,
            'code'      => self::getErrCode(Errno::ERR_SUCESS),
            'usercode'  => !empty($msg)?$msg:self::getErrUsercode(Errno::ERR_SUCESS),
            'data'      => $mixData
        );
        return $arrRet;
    }
    
    public static function error($intErrno, $msg='', $mixData = null)
    {
        $arrRet = array(
            'errno'     => $intErrno,
            'code'      => self::getErrCode($intErrno),
            'usercode'  => !empty($msg)?$msg:self::getErrUsercode($intErrno),
            'data'      => $mixData
        );
        return $arrRet;
    }
    
    public static function errorWithMessage($intErrno, $strMsg = '', $mixData = null)
    {
        return array(
            'errno'     =>  $intErrno,
            'code'      =>  Result::getErrCode($intErrno),
            'usercode'  =>  empty($strMsg) ? Result::getErrUsercode($intErrno) : $strMsg,
            'data'      =>  $mixData
        );
    }
}
