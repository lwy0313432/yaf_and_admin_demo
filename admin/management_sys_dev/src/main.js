// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue';
import Router from 'vue-router';
import App from './App';
import VueResource from 'vue-resource';
import store from './vuex/store';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import 'font-awesome/css/font-awesome.min.css';
import '@/assets/style/main.scss';
import filters from '@/utils/filters';

Vue.config.productionTip = false
Vue.use(Router)
Vue.use(VueResource);
Vue.use(ElementUI);

Object.keys(filters).forEach(key => {
    Vue.filter(key, filters[key])
})
var router = new Router({
    routes: [{
        path: '/',
        meta: {
            auth: true,
        },
        component: function(resolve) {
            require(['./views/Index.vue'], resolve);
        },
        children: [{
            path: '',
            name:'home',
            meta: {
                auth: true,
                action_menu: 'index',
                nav_link: [{
                        title: 'MIS管理系统'
                    },
                    {
                        title: '控制面板'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/Home.vue'], resolve);
            }
        },{
            path: 'authority_menu_list',
            name:'authority_menu_list',
            meta: {
                auth: true,
                action_menu: 'authority_menu_list',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '菜单管理'
                    },
                    {
                        title: '菜单列表'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/authority/authority_menu/authority_menu_list.vue'], resolve);
            }
        },{
            path: 'authority_admin_list',
            name:'authority_admin_list',
            meta: {
                auth: true,
                action_menu: 'authority_admin_list',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '管理员'
                    },
                    {
                        title: '管理员列表'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/authority/authority_admin/authority_admin_list.vue'], resolve);
            }
        },{
            path: 'authority_admin_edit/:id',
            name:'authority_admin_edit',
            meta: {
                auth: true,
                action_menu: 'authority_admin_list',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '管理员'
                    },
                    {
                        title: '管理员编辑'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/authority/authority_admin/authority_admin_edit.vue'], resolve);
            }
        },{
            path: 'authority_admin_add',
            name:'authority_admin_add',
            meta: {
                auth: true,
                action_menu: 'authority_admin_add',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '管理员'
                    },
                    {
                        title: '管理员添加'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/authority/authority_admin/authority_admin_add.vue'], resolve);
            }
        },{
            path: 'usercenter_changePasswd',
            name:'usercenter_changePasswd',
            meta: {
                auth: true,
                action_menu: 'usercenter_changePasswd',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '管理员'
                    },
                    {
                        title: '修改密码'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/usercenter/usercenter_changePasswd.vue'], resolve);
            }
        },{
            path: 'loan_list',
            name:'loan_list',
            meta: {
                auth: true,
                action_menu: 'loan_list',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '借贷管理'
                    },
                    {
                        title: '借贷列表'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/loan/loan_list.vue'], resolve);
            }
        },{
            path: 'loan_editSave/:id',
            name:'loan_editSave',
            meta: {
                auth: true,
                action_menu: 'loan_editSave',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '借贷管理'
                    },
                    {
                        title: '借贷列表',
                        link:'loan_list'
                    },
                    {
                        title: '借贷编辑'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/loan/loan_editSave.vue'], resolve);
            }
        },{
            path: 'loan_audit/:id',
            name:'loan_audit',
            meta: {
                auth: true,
                action_menu: 'loan_audit',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '借贷管理'
                    },
                    {
                        title: '借贷列表',
                        link:'loan_list'
                    },
                    {
                        title: '审核信息'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/loan/loan_audit.vue'], resolve);
            }
        },{
            path: 'loan_showAudit/:id',
            name:'loan_showAudit',
            meta: {
                auth: true,
                action_menu: 'loan_showAudit',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '借贷管理'
                    },
                    {
                        title: '借贷列表',
                        link:'loan_list'
                    },
                    {
                        title: '审核信息'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/loan/loan_showAudit.vue'], resolve);
            }
        },{
            path: 'loan_info/:id',
            name:'loan_info',
            meta: {
                auth: true,
                action_menu: 'loan_info',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '借贷管理'
                    },
                    {
                        title: '借贷列表',
                        link:'loan_list'
                    },
                    {
                        title: '借贷信息'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/loan/loan_info.vue'], resolve);
            }
        },{
            path: 'system_loginLog',
            name:'system_loginLog',
            meta: {
                auth: true,
                action_menu: 'system_loginLog',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '系统管理'
                    },
                    {
                        title: '登录日志'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/system/system_loginLog.vue'], resolve);
            }
        },{
            path: 'user_list',
            name:'user_list',
            meta: {
                auth: true,
                action_menu: 'user_list',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '客户管理'
                    },
                    {
                        title: '客户列表'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/user/user_list.vue'], resolve);
            }
        },{
            path: 'user_edit/:id',
            name:'user_edit',
            meta: {
                auth: true,
                action_menu: 'user_edit',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '客户管理'
                    },
                    {
                        title: '客户列表',
                        link:'user_list'
                    },
                    {
                        title: '客户编辑'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/user/user_edit.vue'], resolve);
            }
        },{
            path: 'user_info/:id',
            name:'user_info',
            meta: {
                auth: true,
                action_menu: 'user_info',
                nav_link: [{
                        title: '个贷管理系统'
                    },
                    {
                        title: '客户管理'
                    },
                    {
                        title: '客户列表',
                        link:'user_list'
                    },
                    {
                        title: '客户详情'
                    }
                ]
            },
            component: function(resolve) {
                require(['./views/user/user_info.vue'], resolve);
            }
        }]
    },{
        path: '/login',
        name:'login',
        component: function(resolve) {
            require(['./views/Login.vue'], resolve);
        }
    }]
})
router.beforeEach((to, from, next) => {
    


        if (to.meta && to.meta.auth) {
            if (to.meta.nav_link) {
                store.dispatch('setRightNavLinks', to.meta.nav_link)
            }
            if (to.meta.action_menu) {
                store.dispatch('setNavMenu', to.meta.action_menu)
            }
            var userinfo = store.getters.getUserInfo;
            if (userinfo.username) {
                next()
            } else {
                //去登录页面,暂时注释起来
                next({ name: 'login' })
            }
        } else {
            next()
        }
    })
Vue.http.options.credentials = true;
Vue.http.options.emulateJSON = true;
// 每个请求设置token
Vue.http.interceptors.push((request, next) => {
    //debugger;
    next((response) => {
        //debugger;
        if (response.code && response.code == 500003) {
             router.push({ name: 'login' });
        }
    });
});
/* eslint-disable no-new */
new Vue({
    el: '#app',
    store,
    router,
    template: '<App/>',
    components: { App }
})