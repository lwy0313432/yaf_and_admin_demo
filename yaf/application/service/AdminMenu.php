<?php
class AdminMenu 
{
    /*
     * 最多支持5级菜单
     */
    public static function get_menu_list()
    {
        $menu_obj=new Dao_Default_AdminMenuModel();
        $menu_list = $menu_obj->where(array('is_del'=>'n'))->order('level asc,id asc')->select();
        
        $arr_out = array();
        $tmp = array();
        $arr_level=array();
        $max_level = 0;
        foreach ($menu_list as $value) {
            $level = $value['level'];
            $flag = $value['flag'];
            $parent_flag = $value['parent_flag'];
            $value['sub']=array();
            $arr_level[$level][$flag]=$value;
            if ($max_level <$level) {
                $max_level = $level;
            }
        }
        
        for ($i= $max_level ; $i>=2;$i--) {
            $sub_list = $arr_level[$i];
            
            $parent_list = $arr_level[$i-1];
            
            foreach ($sub_list as $flag=>$item) {
                $parent_flag = $item['parent_flag'];
                $parent_list[$parent_flag]['sub'][]=$item;
            }
            $arr_level[$i-1] = $parent_list;
            unset($arr_level[$i]);
        }
        $out = $arr_level[1];
        $out = array_values($out);
        return array('menu_list'=>$out);
    }
    /*
     *展示一个管理员的信息，包括他拥有的菜单的权限。
     */
    public static function show_admin_info($param)
    {
        $admin_id = isset($param['admin_id_1']) ?intval($param['admin_id_1']):0;
        $admin_obj=new Dao_Default_AdminModel();
        $admin_info = $admin_obj->where(array('id'=>$admin_id))->select();
        if (!$admin_info) {
            WLog::warning('admin_id_1未设置或管理员不存在',$param);
            throw new CException(Errno::ADMIN_ID_PARAM_NOT_SET);
        }
        $authority = $admin_info[0]['authority'];
        $authority_arr = explode(',', $authority);
        
        $menu_obj=new Dao_Default_AdminMenuModel();
        $menu_list=$menu_obj->where(array('is_del'=>'n'))->order('level asc,id asc')->select();
        $arr_out = array();
        $tmp = array();
        $arr_level=array();
        $max_level = 0;
        foreach ($menu_list as $value) {
            $level = $value['level'];
            $flag = $value['flag'];
            if (in_array($value['flag'], $authority_arr)) {
                $value['has_authority'] ='y';
            } else {
                $value['has_authority'] ='n';
            }
            $parent_flag = $value['parent_flag'];
            $value['sub']=array();
            $arr_level[$level][$flag]=$value;
            if ($max_level <$level) {
                $max_level = $level;
            }
        }
        
        for ($i= $max_level ; $i>=2;$i--) {
            $sub_list = $arr_level[$i];
            
            $parent_list = $arr_level[$i-1];
            
            foreach ($sub_list as $flag=>$item) {
                $parent_flag = $item['parent_flag'];
                $parent_list[$parent_flag]['sub'][]=$item;
            }
            $arr_level[$i-1] = $parent_list;
            unset($arr_level[$i]);
        }
        $out = $arr_level[1];
        $out = array_values($out);
        $data = array(
            'admin_info'=>$admin_info,
            'menu_list'=>$out,
        );
        return $data;
    }
    //展示在后台左侧的菜单列表。超级管理员展示所有的菜单，其他人展示有权限的菜单。
    public static function show_admin_menu($admin_id)
    {
        $admin_id = intval($admin_id);
        if ($admin_id <=0) {
            WLog::warning('admin_id未设置',array('admin_id'=>$admin_id));
            throw new CException(Errno::ADMIN_ID_PARAM_NOT_SET);
        }
        $admin_obj=new Dao_Default_AdminModel();
        $admin_info = $admin_obj->where(array("id"=>$admin_id))->find();
        if (!$admin_info) {
            WLog::warning('管理员不存在',array('admin_id'=>$admin_id));
            throw new CException(Errno::ADMIN_NOT_EXIST);
        }
        
        $role=$admin_info['role'];
        $authority = $admin_info['authority'];
        $authority_arr = explode(',', $authority);
        $menu_obj=new Dao_Default_AdminMenuModel();
        
        $menu_list = $menu_obj->where(array('is_del'=>'n'))->order('level asc,order_num desc,id asc')->select();
         
        $arr_out = array();
        $tmp = array();
        $arr_level=array();
        $max_level = 0;
        foreach ($menu_list as $value) {
            $level = $value['level'];
            $flag = $value['flag'];
            if ($role!='super' && !in_array($value['flag'], $authority_arr)) {
                //即不是超级管理员也没有这个菜单的权限，那么这个菜单就不给他展示了。
                    continue;
            }
            if ($value['is_display'] == 'n') { //如果是隐藏的菜单，则不展示
                    continue;
            }
                
            $parent_flag = $value['parent_flag'];
            $value['sub']=array();
            $arr_level[$level][$flag]=$value;
            if ($max_level <$level) {
                $max_level = $level;
            }
        }
        
        for ($i= $max_level ; $i>=2;$i--) {
            $sub_list = $arr_level[$i];
            
            $parent_list = $arr_level[$i-1];
            
            foreach ($sub_list as $flag=>$item) {
                $parent_flag = $item['parent_flag'];
                $parent_list[$parent_flag]['sub'][]=$item;
            }
            $arr_level[$i-1] = $parent_list;
            unset($arr_level[$i]);
        }
        $out = isset($arr_level[1])?$arr_level[1]:array();
        $out = array_values($out);
        return array('menu_list'=>$out);
    }
    public static function del_menu($param)
    {
        $id = isset($param['id']) ? intval($param['id']) : 0;
        
        if (!$id) {
            WLog::warning('id参数未设置',$param);
            throw new CException(Errno::PARAM_INVALID);
        }
        $menu_obj=new Dao_Default_AdminMenuModel();
        $menu = $menu_obj->where(array('id'=>$id))->find();
        if (!$menu) {
            WLog::warning('要删除的菜单不存在',$param);
            throw new CException(Errno::ADMIN_MENU_NOT_EXIST);
        }
        $sub_menu = $menu_obj->where(array('parent_id'=>$id,'is_del'=>'n'))->find();
        if ($sub_menu) {
            WLog::warning('请先删除子菜单再删除该菜单',$sub_menu);
            throw new CException(Errno::ADMIN_MENU_HAVE_SUB_MENU);
        }
        $arr_in = array('is_del'=>'y');
        $ret = $menu_obj->Update(array('id'=>$id), $arr_in);
        if (!$ret) {
            WLog::warning('数据库操作失败',$param);
            throw new CException(Errno::DB_ERROR);
        }
        return true;
    }
    public static function edit_menu($param)
    {
        $id = isset($param['id']) ? intval($param['id']) : 0;
        $menu_name = isset($param['menu_name']) ? $param['menu_name'] : '';
        $flag = isset($param['menu_flag']) ? $param['menu_flag'] : '';
        $is_display = isset($param['is_display']) ? $param['is_display'] : '';
        $order_num = isset($param['order_num']) ? intval($param['order_num']) : 0;
        if (!$id || !$menu_name || !Tools::is_flag($flag)) {
            WLog::warning('参数不合法：id，menu_name，flag不合法',$param);
            throw new CException(Errno::PARAM_INVALID);
        }
        
        $menu_obj=new Dao_Default_AdminMenuModel();
        $menu = $menu_obj->where(array('flag'=>$flag))->find();
        if ($menu && $menu['id'] != $id) {
            WLog::warning('flag已存在',$param);
            throw new CException(Errno::ADMIN_MENU_FLAG_ALREADY_EXIST);
        }
        if ($is_display !='y' && $is_display!='n') {
            WLog::warning('is_display参数错误',$param);
            throw new CException(Errno::PARAM_INVALID);
        }
        $menu = $menu_obj->where(array('id'=>$id))->find();
        if (!$menu) {
            WLog::warning('菜单不存在',$param);
            throw new CException(Errno::ADMIN_MENU_NOT_EXIST);
        }
        //判断当前待添加的菜单的flag是否符合规范，当前待添加的菜单的flag的前缀必须是以父菜单的flag加下划线开头
        $check_ret = self::check_flag($flag, $menu['parent_flag']);

        //判断子菜单是否有，如果有子菜单，并且子菜单有is_display=y的，那么这个菜单的is_display不能为n
        $sub_menu = $menu_obj->where(array('parent_id'=>$id,"is_display"=>"y"))->find();
        if ($sub_menu && $is_display == 'n') {
            WLog::warning('存在子菜单的is_display=y，所以当前菜单的is_display不能为n',$param);
            throw new CException(Errno::ADMIN_MENU_EDIT_NOT_VALID);
        }
        //判断父菜单，如果父菜单是is_display=n，则子菜单的is_display不能为y
        $parent_menu = $menu_obj->where(array('id'=>$menu['parent_id']))->find();
        if ($parent_menu && $parent_menu[0]['is_display'] == 'n' && $is_display=='y') {
            WLog::warning('父菜单的is_display=n，所以当前菜单的is_display不能为y',$param);
            throw new CException(Errno::ADMIN_MENU_EDIT_NOT_VALID_2);
        }
        $arr_in = array(
            'menu_name'=>$menu_name,
            'flag'=>$flag,
            'order_num'=>$order_num,
            'is_display'=>$is_display,
        );
        $ret = $menu_obj->Update(array('id'=>$id),$arr_in);
        if (!$ret) {
            WLog::warning('数据库操作异常',$param);
            throw new CException(Errno::DB_ERROR);
        }
        return true;
    }
    /*
     * 检查子菜单和父菜单的flag是否合法
     */
    private static function check_flag($sub_flag, $parent_flag)
    {
        $param = array(
            'sub_flag'=>$sub_flag,
            'parent_flag'=>$parent_flag,
        );
        if ($parent_flag == '') {
            if (strpos($sub_flag, "_")) {
                WLog::warning("菜单flag不符合规范，$sub_flag 不能有下划线",$param);
                throw new CException(Errno::ADMIN_MENU_FLAG_NOT_VALID);
            } else {
                return true;
            }
        }
        $length = strlen($parent_flag);
        $flag_prefix = substr($sub_flag, 0, $length+1);
        if ($flag_prefix != $parent_flag."_") {
            WLog::warning("菜单flag不符合规范，必须以父菜单flag加_开头",$param);
            throw new CException(Errno::ADMIN_MENU_FLAG_NOT_VALID_1);
        }
        $flag_real = substr($sub_flag, $length+1);
        
        if (strlen($flag_real) <=0) {
            WLog::warning("菜单flag不符合规范，下划线后面必须有字符",$param);
            throw new CException(Errno::ADMIN_MENU_FLAG_NOT_VALID_2);
        }
        if (strpos($flag_real, "_")) {
            WLog::warning("菜单flag不符合规范，$flag_real 不能有下划线",$param);
            throw new CException(Errno::ADMIN_MENU_FLAG_NOT_VALID_3);
        }
        return true;
    }
    public static function add_menu($param)
    {
        $flag = isset($param['menu_flag']) ? $param['menu_flag'] : '';
        $parent_id = isset($param['parent_id']) ? $param['parent_id'] : 0;
        $menu_name = isset($param['menu_name']) ? $param['menu_name'] : '';
        $is_display = isset($param['is_display']) ? $param['is_display'] : '';
        $order_num = isset($param['order_num']) ? intval($param['order_num']) : 0;
        if (!$menu_name) {
            WLog::warning('menu_name不合法',$param);
            throw new CException(Errno::ADMIN_MENU_NAME_NOT_VALID);
        }
        if (!Tools::is_flag($flag)) {
            WLog::warning('flag不合法',$param);
            throw new CException(Errno::ADMIN_MENU_ADD_FLAG_NOT_VALID);
        }
        if ($is_display!='y' && $is_display !='n') {
            WLog::warning('is_display不合法',$param);
            throw new CException(Errno::ADMIN_MENU_ADD_IS_DISPLAY_NOT_VALID);
        }
        $menu_obj=new Dao_Default_AdminMenuModel();
        $menu = $menu_obj->where(array('flag'=>$flag))->find();
        if (!empty($menu)) {
            WLog::warning('flag已经存在，请换一个',$param);
            throw new CException(Errno::ADMIN_MENU_FLAG_ALREADY_EXIST);
        }
        $parent_id = intval($parent_id);
        if ($parent_id ==0) {
            $parent_flag='';
            $parent_level = 0;
        } else {
            $parent = $menu_obj->where(array('id'=>$parent_id))->find();
            if (!$parent) {
                WLog::warning('父节点不存在',$param);
                throw new CException(Errno::ADMIN_MENU_PARENT_NOT_EXIST);
            }
            $parent_flag=$parent['flag'];
            $parent_level = $parent['level'];
            if ($parent['is_display'] == 'n') {
                WLog::warning('父菜单是不展示的，不能添加子菜单,请先编辑父节点',$param);
                throw new CException(Errno::ADMIN_MENU_ADD_NOT_VALID);
            }
        }
        //判断当前待添加的菜单的flag是否符合规范，当前待添加的菜单的flag的前缀必须是以父菜单的flag加下划线开头
        $check_ret = self::check_flag($flag, $parent_flag);
        $arr_in = array(
            'menu_name'=>$menu_name,
            'flag'=>$flag,
            'parent_id'=>$parent_id,
            'order_num'=>$order_num,
            'parent_flag'=>$parent_flag,
            'level'=>$parent_level+1,
            'is_display'=>$is_display,
            'dt'=>date('Y-m-d H:i:s'),
        );
        $ret = $menu_obj->Insert($arr_in);
        if (!$ret) {
            WLog::warning('添加菜单数据库错误',$param);
            throw new CException(Errno::ADMIN_MENU_ADD_NOT_VALID);
        }
        return array('insert_id'=>$ret);
    }
}
