<?php

/**
 * @package yindou
 * @brief  人脸识别认证基类
 * @author weixiaotong <weixt@yindou.com>
 * @date 2017-12-22
 * @encoding UTF-8
 * @copyright (c) yindou
 */
require_once 'aliyun-openapi-php-sdk/aliyun-php-sdk-core/Config.php';
use Cloudauth\Request\V20171117 as cloudauth;
class SendApi
{
    public function getStatus(){
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", "LTAIoxGsFSmetY3m", "1Ddg9nzhYqWNsED1zA3qsxexfSF14B");
        $iClientProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", "Cloudauth", "cloudauth.aliyuncs.com");
        $client =  new DefaultAcsClient($iClientProfile);
        $request = new cloudauth\GetVerifyTokenRequest();
        $request->setBiz("RPBasic");
        $request->setTicketId("39ecf51e-2f81-4dc5-90ee-ff86125be683-qwert-ads");
        //$response=$client->doAction($request);
        $response = $client->getAcsResponse($request);
        print_r($response);
    }
    
    public function getVerifyToken(){
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", "LTAIoxGsFSmetY3m", "1Ddg9nzhYqWNsED1zA3qsxexfSF14B");
        $iClientProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", "Cloudauth", "cloudauth.aliyuncs.com");
        $client = new DefaultAcsClient($iClientProfile);
        $request = new cloudauth\GetVerifyTokenRequest();
        //$request ->setBiz("RPBasic");
        $request ->setBiz("RPBioOnly");
        $request ->setTicketId("39ecf51e-2f81-4dc5-90ee-ff86125be683-weixt-WX");
        $data=array(
            'Name'=>'周玉震',
            'IdentificationNumber'=>'372321199103099416',
            'IdCardFrontPic'=>'https://caiyunupload.b0.upaiyun.com/photo_uploader/person_loan_upload/f611d579541488584546c686475f8d50.jpg',
        );
        $request ->setBinding(json_encode($data));                     // 根据实际业务情况确定是否需要
        //$request ->setUserData("{\"orderId\": \"1391012388\"}");    // 根据实际业务情况确定是否需要
        //$response=$client->doAction($request);
        $response = $client->getAcsResponse($request);
        print_r($response);
    }
    
}
