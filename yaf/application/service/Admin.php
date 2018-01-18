<?php
class Admin
{
    public static $role=array(
        'service',
        'editor',
        'operator',
        'manager',
        'super',
        'risk',
        'design',
        'devel',
        'marketing',
        'product',
        'accounting'
    );
    public static function dologin($admin_name, $passwd,$vcode)
    {
        $session = Yaf_Session::getInstance();
        $sessionVcode = $session->get('vcode');
        $session->del('vcode');
        if($vcode === '' || $sessionVcode != $vcode ){
            throw new CException(Errno::VCODE_ERR);
        }
        if (!self::check_admin_name($admin_name)) {
            WLog::warning('用户名不合法',array('admin_name'=>$admin_name));
            throw new CException(Errno::ADMIN_USERNAME_INVALID);
        }
        if (!self::check_passwd($passwd)) {
            WLog::warning('密码不合法',array('passwd'=>$passwd));
            throw new CException(Errno::ADMIN_PASSWORD_INVALID);
        }
        //$passwd = self::encode_passwd($passwd);

        $conds=array(
            'username'=>$admin_name,
            'password'=>md5($passwd),
            'is_del'=>0,
        );
        $admin_obj=new Dao_Default_AdminModel();
        $admin_info = $admin_obj->where($conds)->find();

        if (!$admin_info) {
            WLog::warning('用户名或者密码错误',array('passwd'=>$passwd,'admin_name'=>$admin_name));
            throw new CException(Errno::ADMIN_USERNAME_OR_PASSWORD_ERR);
        }
        unset($admin_info['password']);
        //登陆成功了，那么保存session
        self::create_session($admin_info);
        return array('admin_info'=>$admin_info);
    }
    public static function isLogin(){
        $session = Yaf_Session::getInstance();
        if($session->get('adminId') >0 ){
            return array('isLogin'=>'yes');
        }
        return array('isLogin'=>'no');
    }
    public static function get_admin_list($param)
    {
        $is_del = isset($param['is_del']) ? $param['is_del']:0;
        $admin_obj=new Dao_Default_AdminModel();
        $conds=array(
            'is_del'=>$is_del,
        );
        $admin_list = $admin_obj->where($conds)->select();
        return array('admin_list'=>$admin_list);
    }
    public static function update_admin($param)
    {
        $id = isset($param['id']) ? intval($param['id']):0;
        $username = isset($param['username']) ? $param['username']:'';
        $role = isset($param['role']) ? $param['role']:'';
        $real_name = isset($param['real_name']) ? $param['real_name']:'';
        $authority =  isset($param['authority']) ? $param['authority']:'';
        if (!$username || !self::check_admin_name($username)) {
            WLog::warning('用户名不合法',$param);
            throw new CException(Errno::ADMIN_USERNAME_INVALID);
        }
        if ($id <=0) {
            WLog::warning('编辑管理员，被编辑人员的id未设置',$param);
            throw new CException(Errno::ADMIN_ID_PARAM_NOT_SET);
        }
        if (!$role || !in_array($role, self::$role)) {
            WLog::warning('用户角色设置不对',$param);
            throw new CException(Errno::ADMIN_ROLE_INVALID);
        }
        if (!$real_name) {
            WLog::warning('用户真实姓名设置不对',$param);
            throw new CException(Errno::ADMIN_REALNAME_INVALID);
        }
        $arr_update = array(
            'username'=>$username,
            'role'=>$role,
            'realname'=>$real_name,
            'authority'=>$authority
        );
        $admin_obj=new Dao_Default_AdminModel();
        $ret = $admin_obj->Update(array('id'=>$id), $arr_update);

        if (false===$ret) { //只有返回错误的情况才是更新失败。如果返回0 那么只是表示，这个更新，影响记录的条数数0
            WLog::warning('更新管理员数据库错误',$param);
            throw new CException(Errno::DB_ERROR);
        } else {
            return true;
        }
    }
    public static function add_admin($param)
    {
        $username = isset($param['username']) ? $param['username']:'';
        $role = isset($param['role']) ? $param['role']:'';
        //	$email = isset($param['email']) ? $param['email']:'';
        $real_name = isset($param['real_name']) ? $param['real_name']:'';
        if (!$username || !self::check_admin_name($username)) {
            WLog::warning('添加管理员，用户名不合法',$param);
            throw new CException(Errno::ADMIND_USERNAME_INVALID);
        }
        $admin_obj=new Dao_Default_AdminModel();
        $admin=$admin_obj->where(array('username'=>$username))->find();
        if ($admin) {
            WLog::warning('添加管理员，用户名已存在',$param);
            throw new CException(Errno::ADMIN_USERNAME_ALREADY_EXIST);
        }
        $password = isset($param['password'])?$param['password']:'';

        if (!$password || !Util::is_mis_valid_passwd($password)) {
            WLog::warning('密码不合法，必须包含大写字母，小写字母，特殊字符，数字，8位以上',$param);
            throw new CException(Errno::ADMIN_PASSWORD_INVALID);
        }
        if (!$role || !in_array($role, self::$role)) {
            WLog::warning('添加管理员，用户角色设置不对',$param);
            throw new CException(Errno::ADMIN_ROLE_INVALID);
        }
        if (!$real_name) {
            WLog::warning('添加管理员，用户真实姓名设置不对',$param);
            throw new CException(Errno::ADMIN_REALNAME_INVALID);
        }
        $password = md5($password);
        $arr_in=array(
            'username'=>$username,
            'role'=>$role,
            'realname'=>$real_name,
            'password'=>$password,
            'authority'=>'welcom',
        );
        $insert_id =$admin_obj->Insert($arr_in);
        if (!$insert_id) {
            WLog::warning('添加管理员，数据库错误',$arr_in);
            throw new CException(Errno::DB_ERROR);
        } else {
            return array('insert_id'=>$insert_id);
        }
    }

    public static function register_email($email)
    {
        $subject='APP管理后台权限开通';
        $email_obj=new Email_Send();
        $email_obj->send_mail($email, $subject, 'nnd');
    }
    public static function del_admin($param)
    {
        $admin_id = isset($param['id']) ? intval($param['id']) :0;
        if ($admin_id<=0) {
            WLog::warning('用户id错误或未登陆',$param);
            throw new CException(Errno::ADMIN_ID_PARAM_NOT_SET);
        }
        $arr_in = array('is_del'=>1);
        $admin_obj=new Dao_Default_AdminModel();
        $ret =  $admin_obj->Update(array('id'=>$admin_id), $arr_in);
        if (!$ret) {
            WLog::warning('内部错误',$param);
            throw new CException(Errno::DB_ERROR);
        } else {
            return true;
        }
    }
    public static function get_admin_info($admin_id)
    {
        $admin_obj=new Dao_Default_AdminModel();
        $admin_id = isset($admin_id) ? intval($admin_id):0;
        if ($admin_id <=0) {
            WLog::warning('用户id错误或未登陆',array("id"=>$admin_id));
            throw new CException(Errno::ADMIN_ID_PARAM_NOT_SET);
        }
        $admin_info = $admin_obj->where(array("id"=>$admin_id))->find();

        if (!$admin_info) {
            WLog::warning('用户不存在',array("id"=>$admin_id));
            throw new CException(Errno::ADMIN_NOT_EXIST);
        }
        return array('admin_info'=>$admin_info);
    }

    public static function change_passwd($adminId,$param)
    {
        $admin_id = $adminId;
        if(intval($admin_id) != $admin_id || intval($admin_id) <=0 ){
            
            WLog::warning('用户id错误或未登陆',$param);
            throw new CException(Errno::ADMIN_ID_PARAM_NOT_SET);
        }
        $admin_id = intval($admin_id);
        $old_passwd = isset($param['old_passwd']) ? $param['old_passwd']: '';
        $new_passwd = isset($param['new_passwd']) ? $param['new_passwd']: '';
        $admin_obj=new Dao_Default_AdminModel();
        $admin_info=$admin_obj->where(array('id'=>$admin_id))->find();

        if (!$admin_info) {
            WLog::warning('用户不存在',$param);
            throw new CException(Errno::ADMIN_NOT_EXIST);
        }
        if (md5($old_passwd) != $admin_info['password']) {
            WLog::warning('旧密码错误',$param);
            throw new CException(Errno::ADMIN_OLD_PASSWD_ERR);
        }
        if (!Util::is_mis_valid_passwd($new_passwd)) {
            WLog::warning('密码格式为8位以上含大小写字母数字@-_.',$param);
            throw new CException(Errno::ADMIN_PASSWORD_INVALID);
        }
        $new_passwd_encode = md5($new_passwd);
        $arr_in = array(
            'password'=>$new_passwd_encode,
        );
        $conds = array(
            'id'=>$admin_id,
        );
        $ret =$admin_obj->Update($conds, $arr_in);
        if (!$ret) {
            WLog::warning('数据库错误',$param);
            throw new CException(Errno::DB_ERROR);
        } else {
            return array('state'=>'success');
        }
    }
    public static function check_admin_name($admin_name)
    {
        return preg_match('/^[-0-9A-Za-z_]{5,20}$/', $admin_name);
    }
    public static function check_passwd($passwd)
    {
        return Util::is_mis_valid_passwd($passwd);
    }
    public static function encode_passwd($passwd)
    {
        return md5(Yaf_Application::app()->getConfig()->md5head.md5($passwd));
    }
    public static function logout()
    {
        self::destroy_session();
    }
    private static function destroy_session()
    {
        $session = Yaf_Session::getInstance();
        $session->del('adminId');
    }
    private static function create_session($admin_info)
    {
        $session = Yaf_Session::getInstance();
        $session->set('adminId',$admin_info['id']);
    }
    public static function getAdminIdFromSession(){
        $adminId = 0;
        $session = Yaf_Session::getInstance();
        $adminId = $session->get('adminId');
        return $adminId;
    }   
    public static function getAdminInfo($adminId){
        if(intval($adminId) <= 0){ 
            WLog::warning('param_err',array('adminId'=>$adminId,));
            throw new CException(Errno::PARAM_INVALID);
        }   
        $adminId = intval($adminId);
        $daoAdmin = new Dao_Default_AdminModel();
        $info = $daoAdmin->where(array('id'=>$adminId))->find();
        if(!$info){
            return array();
        }   
        return $info;
    }
}
