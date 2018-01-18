<template>
    <el-form :inline="true" :model="form" class="loan-form-wrap">
        <div class="flex">
            <el-form-item label="身份证号:" class="flex-item">
                <div v-text="form.id_no">110283484994</div>
            </el-form-item>
            <el-form-item label="储蓄卡号:" class="flex-item">
                <div v-text="form.open_acct_id">56434 39060 56456 45656 656</div>
            </el-form-item>
        </div>
        <div class="flex">
            <el-form-item label="开户行:" class="flex-item">
                <div v-text="form.bank_name">北京人民银行上地支行</div>
            </el-form-item>
        </div>
        <div class="flex papers">
            <img :src="form.img1" :class="isScale?'img1':''" @click="imgScale(1)">
            <img :src="form.img2" :class="isScale2?'img1':''" @click="imgScale(2)">
        </div>
        <div class="check-result-label">天机风控系统检测如下：</div>
        <!-- <div class="check-result" v-text="form.checkRes">
        </div> -->
        <ul class="check-result">
            <span v-show="checkLoading">正在检测中...</span>
            <li v-for="(index) in form.resArr" :key="index">{{index}}</li>
        </ul>
        <div class="flex">
            <el-button @click="doCheck()">风控检测</el-button>
        </div>
        <div class="audit-status" v-if="form.state == 2">审核状态：<span style="color:red;">通过</span></div>
        <div class="audit-status" v-if="form.state == 3">审核状态：<span style="color:red;">驳回</span></div>
        <div class="feedback" v-if="form.state == 3">
            <div>驳回原因:</div>
            <el-input type="textarea" style="width: 580px;margin-top: 10px;" :rows="5" v-model="form.remark" placeholder="不通过原因标注" readonly></el-input>
        </div>
        <!-- 审核信息 1待审核 2审核通过 3审核失败-->
        <div class="flex" style="margin-top: 28px;" v-if="form.state != 1">
            <el-form-item label="操作人员:">
                <div v-text="form.admin_name">关羽</div>
            </el-form-item>
            <el-form-item label="操作时间:" style="margin-left:20px;">
                <div v-text="form.audit_time"></div>
            </el-form-item>
        </div>
        <!-- 审核 -->
        <div class="flex" v-if="form.state == 1">
            <el-form-item style="margin: 20px auto;">
                <el-button type="primary" @click="audit('2')">审核通过</el-button>
                <el-button type="primary" @click="isAudit()">审核不通过</el-button>
            </el-form-item>
        </div>
        <el-dialog title="审核不通过" :visible.sync="dialogVisible" width="30%">
            <el-form :model="form">
                <el-input type="textarea" :rows="4" v-model="form.remark" placeholder="不通过原因标注"></el-input>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button @click="dialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="audit('3')">确 定</el-button>
            </span>
        </el-dialog>
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
                form:{
                    id:'',
                    admin_name:'',
                    id_no:'',
                    open_acct_id:'',
                    audit_time:'',
                    state:'',
                    bank_name:'',
                    img1:'',
                    img2:'',
                    checkRes:'',
                    resArr:'',
                    remark:''
                },
                dialogVisible:false,
                checkLoading:false,
                isScale:false,
                isScale2:false
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
                api.loan_show({
                    person_loan_id:id
                })
                .then((data)=>{
                    // console.log(JSON.stringify(data))
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        // person_loan_type 1为身份证正面 2为身份证背面
                        let _list = _data.data.list;
                        for(let i=0;i<_list.length;i++){
                            if(_list[i].person_loan_type==1){
                                self.form.img1 = _list[i].filename;
                            }else if(_list[i].person_loan_type==2){
                                self.form.img2 = _list[i].filename;
                            }
                        }
                        self.form.id_no = _data.data.id_no;
                        self.form.admin_name = _data.data.admin_name;
                        self.form.open_acct_id = _data.data.open_acct_id;
                        self.form.state = _data.data.state;
                        self.form.bank_name=_data.data.bank_name;
                        self.form.audit_time = _data.data.audit_time;
                        self.form.remark = _data.data.remark;
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
            isAudit(){ 
                this.dialogVisible = true;
            },
            audit(type){//审核 type 1待审核 2审核通过 3审核不通过
                let remark = "";
                if(type == 3){
                    remark = this.form.remark;
                }
                let self=this;
                api.loan_verify({
                    person_loan_id:this.form.id,
                    state:type,
                    remark:remark
                })
                .then((data)=>{
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        this.$message({
                            message: '审核成功',
                            type: 'success'
                        });
                        this.$router.push({name:'loan_list'});
                    }else{
                        this.$message.error(_data.message);
                    }
                })
                .catch((error)=>{
                    self.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            doCheck(){
                this.checkLoading=true;
                let self=this;
                api.loan_credit({
                    person_loan_id:self.form.id
                })
                .then((data)=>{
                    this.checkLoading=false;
                    // console.log(JSON.stringify(data))
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        if(_data.data.list.personAntiSpoofingDescInfo){
                            this.form.resArr = _data.data.list.personAntiSpoofingDescInfo.split("。");
                        }else{
                            this.$message.error("未检测到结果");
                        }
                        // this.form.checkRes = _data.data.list.personAntiSpoofingDescInfo;
                    }else{
                        this.$message.error(_data.message);
                    }
                })
                .catch((error)=>{
                    this.checkLoading=false;
                    self.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            imgScale(type){
                if(type==1){
                    this.isScale=!this.isScale;
                    this.isScale2=false;
                }else{
                    this.isScale2=!this.isScale2;
                    this.isScale=false;
                }
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
.check-result{
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 20px;
    margin: 10px 0;
    min-height: 180px;
}

.papers img{cursor: pointer;}
.papers .img1{width:600px !important;height:400px !important;}



.admin_list_title{margin-bottom:10px;}
.el-badge:first-child{margin-left: 0;}
.el-badge{margin-left: 50px;}
</style>
