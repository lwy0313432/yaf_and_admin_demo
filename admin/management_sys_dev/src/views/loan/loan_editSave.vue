<template>
    <el-form :inline="true" :model="form" class="loan-form-wrap">
        <div class="flex">
            <el-form-item label="客户编号" class="flex-item">
                <div v-text="form.user_id"></div>
            </el-form-item>
            <el-form-item label="注册时间" class="flex-item">
                <div v-text="form.user_dt"></div>
            </el-form-item>
        </div>

        <div class="flex">
            <el-form-item label="推荐人手机" class="flex-item">
                <div v-text="form.agent_mobile"></div>
            </el-form-item>
        </div>
        <div class="flex">
            <el-form-item label="客户手机" class="flex-item">
                <el-input v-model="form.mobile"></el-input>
            </el-form-item>
            <el-form-item label="客户邮箱" class="flex-item">
                <el-input v-model="form.email"></el-input>
            </el-form-item>
        </div>
        <div class="flex">
            <el-form-item label="客户姓名" class="flex-item">
                <el-input v-model="form.realname"></el-input>
            </el-form-item>
            <el-form-item label="客户性别" class="flex-item">
                <el-select v-model="form.gender">
                    <el-option label="男" value="1"></el-option>
                    <el-option label="女" value="2"></el-option>
                </el-select>
            </el-form-item>
        </div>
        <div class="flex">
            <el-form-item label="借款金额" class="flex-item">
                <el-input v-model="form.money"></el-input>
            </el-form-item>
            <el-form-item label="借款期限" class="flex-item">
                <el-input v-model="form.term_loan"></el-input>
            </el-form-item>
        </div>
        <div class="flex">
            <el-form-item label="借款用途" class="flex-item">
                <el-select v-model="form.use_funds">
                    <el-option
                        v-for="item in user_funds_arr"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="所属城市" class="flex-item">
                <el-input v-model="form.city"></el-input>
            </el-form-item>
        </div>
        <div class="flex">
            <el-form-item label="账户状态" class="flex-item">
                <el-select v-model="form.is_close">
                <el-option label="启用" value="0"></el-option>
                <el-option label="停用" value="1"></el-option>
                </el-select>
            </el-form-item>
        </div>
        <div class="flex">
            <el-form-item label="备注" style="letter-spacing: 10px;">
                <el-input type="textarea" style="width: 535px;" :rows="5" v-model="form.user_remark"></el-input>
            </el-form-item>
        </div>
        <div class="flex" style="width: 70%;">
            <el-form-item style="margin: 0 auto;">
                <el-button type="primary" @click="doSave()">保 存</el-button>
            </el-form-item>
        </div>
    </el-form>
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
            return {
                form: {
                    id:'',
                    realname:'',
                    email:'',
                    mobile:'',
                    gender:'',
                    agent_mobile:'',
                    money:'',
                    term_loan:'',
                    city:'',
                    user_dt:'',
                    user_id:'',
                    is_close:'',
                    use_funds:'',
                    user_remark:'',
                },
                user_funds_arr:[]
            }
        },
        mounted: function() {
        },
        computed: {
            ...mapGetters(['getUserInfo', 'getLeftBig', 'getRightNavLinks', 'getNavMenu']),
        },
        methods: {
            ...mapActions(['removeUserInfo']),
            load_data(id){
                let self=this;
                api.loan_info({
                    person_loan_id:id
                })
                .then((data)=>{
                    // console.log(JSON.stringify(data));
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        let ld = _data.data.list[0];
                        self.form.id = ld.id;
                        self.form.realname=ld.realname;
                        self.form.mobile=ld.mobile;
                        self.form.email = ld.email;
                        self.form.gender = ld.gender;
                        self.form.money=ld.money;
                        self.form.term_loan=ld.term_loan;
                        self.form.city=ld.city;
                        self.form.user_dt=ld.user_dt;
                        self.form.user_id=ld.user_id;
                        self.form.is_close=ld.is_close;
                        self.form.use_funds=ld.use_funds;
                        self.form.agent_mobile = ld.agent_mobile;
                        self.form.user_remark=ld.user_remark;
                        let _arr = [];let obj = null;
                        for(let item in _data.data.user_funds_arr){
                            if(_data.data.user_funds_arr.hasOwnProperty(item) === true){
                                obj = {'value':item,'label':_data.data.user_funds_arr[item]}
                                _arr.push(obj);
                            }
                        }
                        self.user_funds_arr = _arr;
                    }else{
                        self.$message.error({message:_data.message})
                    }
                })
                .catch((error)=>{
                    self.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            doSave(){
                let self=this;
                api.loan_edit({
                    person_loan_id:self.form.id,
                    email:self.form.email,
                    realname:self.form.realname,
                    gender:self.form.gender,
                    money:self.form.money,
                    term_loan:self.form.term_loan,
                    use_funds:self.form.use_funds,
                    user_remark:self.form.user_remark,
                    city:self.form.city,
                    is_close:self.form.is_close
                })
                .then((data)=>{
                    // console.log(JSON.stringify(data));
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        self.$message({message: '保存成功',type: 'success'});
                        self.$router.push({name:'loan_list'});
                    }else{
                        self.$message.error({message:_data.message})
                    }
                })
                .catch((error)=>{
                    self.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            change(){
                let id = this.$route.params.id;
                this.form.id = id;
                this.load_data(id);
            }
        },
        created: function() {
            this.change();
        },
        watch: {
          "$route": "change"// 如果路由有变化，会再次执行该方法
        }
    }
</script>
<style>
</style>
