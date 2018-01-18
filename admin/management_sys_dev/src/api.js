import Vue from 'vue'
import { WEBAPI_ROOT, WEBPAYAPI_ROOT } from './config'
export default {
    dologin(params){
        //登录
        const req = Vue.http.post(WEBAPI_ROOT + '/dologin',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    islogin(params){
        //是否登录
        const req = Vue.http.post(WEBAPI_ROOT + '/islogin',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    logout(){
        //退出
        const req = Vue.http.post(WEBAPI_ROOT + '/logout');
        return req.then((response) => Promise.resolve(response.data));
    },
    admin_my_menu() {
        //我的菜单·
        const req = Vue.http.post(WEBAPI_ROOT + '/admin_my_menu');
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_menu_list() {
        //菜单列表
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_menu_list');
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_menu_add(params){//添加菜单
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_menu_add',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_admin_list(){//管理员列表
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_admin_list');
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_admin_add(params){//添加管理员
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_admin_add',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_admin_del(params){//删除管理员
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_admin_del',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_admin_getinfo(params){//管理员权限列表
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_admin_getinfo',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_admin_update(params){//修改管理员信息
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_admin_update',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_menu_del(params){//删除菜单
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_menu_del',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    authority_menu_edit(params){//编辑菜单
        const req = Vue.http.post(WEBAPI_ROOT + '/authority_menu_edit',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    usercenter_changePasswd(params){//修改密码
        const req = Vue.http.post(WEBAPI_ROOT + '/usercenter_changePasswd',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_list(params){//借贷信息列表
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_list',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    user_close(params){//账号停用
        const req = Vue.http.post(WEBAPI_ROOT + '/user_close',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_audit(params){//客户审核
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_audit',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_editSave(params){//客户信息编辑保存
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_editSave',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_info(params){//客户信息查看
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_info',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    system_loginLog(){//登录日志
        const req = Vue.http.post(WEBAPI_ROOT + '/system_loginLog');
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_info(params){//信息管理-查看
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_info',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_edit(params){//信息管理-修改
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_edit',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    user_edit(params){//修改用户信息
        const req = Vue.http.post(WEBAPI_ROOT + '/user_edit',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_show(params){//审核结果信息
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_show',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_credit(params){//风控检测
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_credit',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    loan_verify(params){//借贷信息审核
        const req = Vue.http.post(WEBAPI_ROOT + '/loan_verify',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    user_list(params){//客户列表
        const req = Vue.http.post(WEBAPI_ROOT + '/user_list',params);
        return req.then((response) => Promise.resolve(response.data));
    },
    user_info(params){//客户详情
        const req = Vue.http.post(WEBAPI_ROOT + '/user_info',params);
        return req.then((response) => Promise.resolve(response.data));
    }

    

     


    


    

    

    
}