<?php
/**
 * @describe:后台管理系统的权限控制
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */
class AdminAuthCheck 
{
    public static function check($adminId,$flag,$controller=''){
        if($controller == 'admin' && $flag=='index'){  // url= /admin/index 时也不做权限判断
            return true;
        }
        if(in_array($flag,Config::$adminNotNeedAuthController)){
            return true;
        }
        $adminId = intval($adminId);
        if($adminId<=0){
            throw new CException(Errno::ADMIN_NOT_LOGIN);
        }
        $adminObj = new Dao_Default_AdminModel();
        $adminInfo = $adminObj->where(array('id'=>$adminId))->find();
        if($adminInfo['role'] ==='super'){//超级管理员，拥有所有权限
            return true;
        }
        if(!$adminInfo ||!isset($adminInfo['authority']) ) {
            throw new CException(Errno::ADMIN_NOT_EXIST);
        }

        $authArr = explode(",",$adminInfo['authority']);
        if($authArr){
            foreach($authArr as $key=> $value){
                $authArr[$key] = strtolower($value);
            }
        }
        if(!in_array($flag,$authArr)){
            throw new CException(Errno::ADMIN_PERMISSION_DENY);
        }
        return true;

    }
}
