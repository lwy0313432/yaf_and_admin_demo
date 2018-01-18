<template>
    <div>
            <el-form :rules="rules" ref="form" :model="form" label-width="80px" style="width:600px;">
                <el-form-item label="旧密码" prop="oldPassword">
                    <el-input type="password" v-model="form.oldPassword"></el-input>
                </el-form-item>
                <el-form-item label="新密码" prop="password">
                    <el-input type="password" v-model="form.password"></el-input>
                </el-form-item>
                <el-form-item label="重复密码" prop="password2">
                    <el-input type="password" v-model="form.password2"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="admin_add_btns">
                <el-button @click="reset('form')">重 置</el-button>
                <el-button type="primary" @click="changePwd('form')">修 改</el-button>
            </div>
    </div>
</template>
<script>
    import api from '../../api'
    import {
        mapGetters,
        mapActions
    } from 'vuex'
    import Vali from '../../utils/validate_rules'
    import util from '../../utils/util'
    export default {
        data: function() {
            var validatePass = (rule, value, callback) => {
                if(value === ''){
                    callback(new Error('请输入密码'));
                }else if (!/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/.test(value)) {
                    callback(new Error("密码不合法，必须包含大写字母，小写字母，特殊字符，数字，8位以上"));
                } else {
                    if (this.form.password2 !== '') {
                        this.$refs.form.validateField('password2');
                    }
                    callback();
                }
            };
            var validatePass2 = (rule, value, callback) => {
                if(value === ''){
                    callback(new Error('请再次输入密码'));
                }else if (value !== this.form.password) {
                    callback(new Error('两次输入密码不一致!'));
                } else {
                    callback();
                }
            };
            return {
                form: {
                    oldPassword: '',
                    password: '',
                    password2:''
                },
                rules:{
                    oldPassword: [
                        {required: true,  trigger: 'blur',message:'请输入旧密码' }
                    ],
                    password: [
                        {required: true,validator: validatePass,  trigger: 'blur' }
                    ],
                    password2: [
                        {required: true,validator: validatePass2,  trigger: 'blur' }
                    ]
                }
            }
        },
        mounted: function() {
            
    
        },
        computed: {
            ...mapGetters(['getUserInfo', 'getLeftBig', 'getRightNavLinks', 'getNavMenu']),
        },
        methods: {
            ...mapActions(['removeUserInfo']),
            changePwd(formName){
                this.$refs[formName].validate((valid) => {
                    console.log(valid);
                    if (valid) {
                        console.log("提交数据："+this.form.oldPassword+"_"+this.form.password+"_"+this.form.password2)
                        api.usercenter_changePasswd({
                            old_passwd:this.form.oldPassword,
                            new_passwd:this.form.password
                        })
                        .then((data)=>{
                            var _data = util.formatJson(data);
                            console.log(_data)
                            if (_data.code == 0) {
                                this.$message({
                                    message: '修改成功',
                                    type: 'success'
                                });
                                this.reset('form');
                                // this.$router.push({name:'login'});
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
            reset(formName){
                this.$refs[formName].resetFields();
            }
        },
        created: function() {
        }
    }
</script>
<style>
.admin_add_btns{margin-left: 80px;}
</style>
