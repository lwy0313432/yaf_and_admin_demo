<template>
    <el-form :inline="true" :model="form" class="loan-form-wrap">
        <div class="flex">
            <el-form-item label="客户编号:" class="flex-item">
                <div v-text="form.id">242423235235</div>
            </el-form-item>
            <el-form-item label="注册时间:" class="flex-item">
                <div v-text="form.dt">2018-12-23    12：23：22</div>
            </el-form-item>
        </div>
        <div class="flex">
            <el-form-item label="客户手机:" class="flex-item">
                <div v-text="form.mobile"></div>
            </el-form-item>
            <el-form-item label="推荐人ID:" class="flex-item">
                <el-input v-model="form.agent_mobile" readonly></el-input>
            </el-form-item>
        </div>
        <el-form-item label="账户状态:" class="flex-item">
            <el-select v-model="form.is_close" disabled>
            <el-option label="启用" value="0"></el-option>
            <el-option label="停用" value="1"></el-option>
            </el-select>
        </el-form-item>
        <!-- <div class="flex">
            <el-form-item label="备注" style="letter-spacing: 14px;" class="flex-item">
                <el-input type="textarea" style="width: 500px;" :rows="5" v-model="form.user_remark" readonly></el-input>
            </el-form-item>
        </div> -->
        <div class="flex" style="    width: 70%;">
            <el-form-item style="margin: 0 auto;">
                <el-button type="primary" @click="doReturn()">返 回</el-button>
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
                    mobile:'',
                    dt:'',
                    is_close:'',
                    agent_mobile:''
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
            load_data(id){
                let self=this;
                api.user_info({
                    user_id:id
                })
                .then((data)=>{
                    // console.log(JSON.stringify(data));
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        self.form.id=_data.data.list.id;
                        self.form.mobile=_data.data.list.mobile;
                        self.form.dt=_data.data.list.dt;
                        self.form.is_close=_data.data.list.is_close;
                        self.form.agent_mobile=_data.data.list.agent_mobile;
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
            doReturn(){//返回
                this.$router.push({name:'user_list'});
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



.admin_list_title{margin-bottom:10px;}
.el-badge:first-child{margin-left: 0;}
.el-badge{margin-left: 50px;}
</style>
