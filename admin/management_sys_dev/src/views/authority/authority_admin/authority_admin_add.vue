<template>
    <div>
            <el-form :rules="rules" ref="form" :model="form" label-width="80px" style="width:600px;">
                <el-form-item label="登录名" prop="username">
                    <el-input v-model="form.username"></el-input>
                </el-form-item>
                <el-form-item label="密码" prop="password">
                    <el-input v-model="form.password"></el-input>
                </el-form-item>
                <el-form-item label="中文名" prop="real_name">
                    <el-input v-model="form.real_name"></el-input>
                </el-form-item>
                <el-form-item label="角色" prop="role">
                    <el-select v-model="form.role" placeholder="请选择">
                        <el-option
                        v-for="item in roles"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-form>
            <div slot="footer" class="admin_add_btns">
                <el-button @click="cancel()">返 回</el-button>
                <el-button type="primary" @click="addAdmin('form')">保 存</el-button>
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
                form: {
                    username: '',
                    password: '',
                    role:'',
                    real_name:'',
                },
                rules:{//验证规则
                    username: [
                        { required: true, message: '输入项不能为空', trigger: 'blur' }
                    ],
                    password: [
                        { required: true, message: '输入项不能为空', trigger: 'blur' }
                    ],
                    real_name: [
                        { required: true, message: '输入项不能为空', trigger: 'blur' }
                    ],
                    role: [
                        { required: true, message: '输入项不能为空', trigger: 'change' }
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
            addAdmin(formName){
                this.$refs[formName].validate((valid) => {
                    if (valid) {//验证通过 提交数据
                        // console.log("提交数据："+this.form.username+"_"+this.form.password+"_"+this.form.real_name+"_"+this.form.role)
                        api.authority_admin_add({
                            username:this.form.username,
                            password:this.form.password,
                            real_name:this.form.real_name,
                            role:this.form.role
                        })
                        .then((data)=>{
                            var _data = util.formatJson(data);
                            // console.log(_data)
                            if (_data.code == 0) {
                                this.$message({
                                    message: '添加成功',
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
            }
        },
        created: function() {
        }
    }
</script>
<style>
.admin_add_btns{margin-left: 80px;}
</style>
