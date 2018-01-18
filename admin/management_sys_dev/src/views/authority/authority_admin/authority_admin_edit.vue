<template>
    <div>
            <el-form :rules="rules" ref="form" :model="admin_info" label-width="80px" style="width:600px;">
                <el-form-item label="登录名" prop="username">
                    <el-input v-model="admin_info.username"></el-input>
                </el-form-item>
                <el-form-item label="中文名" prop="real_name">
                    <el-input v-model="admin_info.real_name"></el-input>
                </el-form-item>
                <el-form-item label="角色">
                    <el-select v-model="admin_info.role" placeholder="请选择">
                        <el-option
                        v-for="item in roles"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="权限">
                    <el-tree
                        :data="menu_list"
                        show-checkbox
                        node-key="flag"
                        ref="tree"
                        default-expand-all
                        check-strictly
                        :default-checked-keys="auth_list"
                        :props="defaultProps">
                    </el-tree>
                </el-form-item>
            </el-form>
            <div slot="footer" class="admin_add_btns">
                <el-button @click="cancel()">返 回</el-button>
                <el-button type="primary" @click="editSaveAdmin('form')">更 新</el-button>
            </div>
    </div>
</template>
<script>
    import api from '../../../api'
    import {
        mapGetters,
        mapActions
    } from 'vuex'
    import Vali from '../../../utils/validate_rules'
    import util from '../../../utils/util'
    
    export default {
        data: function() {
            return {
                admin_info:{
                    id:'',
                    username:'',
                    real_name:'',
                    role:''
                },
                rules:{
                    username: [
                        { required: true, message: '输入项不能空', trigger: 'blur' }
                    ],
                    real_name: [
                        { required: true, message: '输入项不能空', trigger: 'blur' }
                    ],
                },
                menu_list:[],
                auth_list:[],
                defaultProps: {
                    children: 'sub',
                    label: 'menu_name'
                },
                roles: [{
                    value: 'service',
                    label: 'service'
                }, {
                    value: 'editor',
                    label: 'editor'
                }, {
                    value: 'operator',
                    label: 'operator'
                }, {
                    value: 'manager',
                    label: 'manager'
                }, {
                    value: 'super',
                    label: 'super'
                }, {
                    value: 'risk',
                    label: 'risk'
                }, {
                    value: 'design',
                    label: 'design'
                }, {
                    value: 'devel',
                    label: 'devel'
                }, {
                    value: 'marketing',
                    label: 'marketing'
                }, {
                    value: 'product',
                    label: 'product'
                }, {
                    value: 'accounting',
                    label: 'accounting'
                }],
                value: '',
            }
        },
        mounted: function() {
            
    
        },
        computed: {
            ...mapGetters(['getUserInfo', 'getLeftBig', 'getRightNavLinks', 'getNavMenu']),
        },
        methods: {
            ...mapActions(['removeUserInfo']),
            load_admin_info(id){
                let self=this;
                api.authority_admin_getinfo({
                    admin_id_1:id
                })
                .then((data)=>{
                    var _data = util.formatJson(data);
                    // console.log(JSON.stringify(data))
                    if (_data.code == 0) {
                        self.admin_info.username = _data.data.admin_info[0].username;
                        self.admin_info.real_name = _data.data.admin_info[0].realname;
                        self.admin_info.role = _data.data.admin_info[0].role;
                        self.menu_list = _data.data.menu_list;
                        // 管理员权限
                        let authority = _data.data.admin_info[0].authority;
                        authority = authority.split(",");
                        self.auth_list = authority;
                    }else{
                        this.$message.error(_data.message);
                        this.$router.push({name:'authority_admin_list'});
                    }
                })
                .catch((error)=>{
                    self.$message.error({
                        message: '未知错误,请联系管理员！'
                    });

                })
            },
            editSaveAdmin(formName){
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        let _authority = this.$refs.tree.getCheckedKeys().join(",");//得到所有选中节点keys
                        // console.log("提交数据："+this.admin_info.id+"_"+this.admin_info.username+"_"+this.admin_info._+"_"+this.admin_info.role+"_"+_authority)
                        api.authority_admin_update({
                            id:this.admin_info.id,
                            username:this.admin_info.username,
                            real_name:this.admin_info.real_name,
                            role:this.admin_info.role,
                            authority:_authority
                        })
                        .then((data)=>{
                            var _data = util.formatJson(data);
                            // console.log(_data)
                            if (_data.code == 0) {
                                this.$message({
                                    message: '修改成功',
                                    type: 'success'
                                });
                                this.$router.push({name:'authority_admin_list'});
                            }else{
                                this.$message.error(_data.message);
                            }
                        })
                        .catch((error)=>{
                            this.$message.error({
                                message: '未知错误,请联系管理员！'
                            });
                        })
                    } else {
                        return false;
                    }
                });
            },
            cancel(){
                this.$router.push({name:'authority_admin_list'});
            },
            change(){
                let id = this.$route.params.id;
                this.admin_info.id = id;
                this.load_admin_info(id);
            }
        },
        created: function() {
            this.change();
        },
        watch: {
          // 如果路由有变化，会再次执行该方法
          "$route": "change"
        }
    }
</script>
<style>
.admin_add_btns{margin-left: 80px;}
</style>
