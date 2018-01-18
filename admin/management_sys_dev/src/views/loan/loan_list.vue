<template>
    <div>
        <div class="admin_list_title">
                <el-badge :value="auditStatusAll" class="item">
                    <el-button size="small" @click="auditStatusChange('','tag1')" :class="statusTag=='tag1'?'acitved':''">全部客户</el-button>
                </el-badge>
                <el-badge :value="auditStatusPre" class="item">
                    <el-button size="small" @click="auditStatusChange('1','tag2')" :class="statusTag=='tag2'?'acitved':''">待审核</el-button>
                </el-badge>
                <el-badge :value="auditStatusPass" class="item">
                    <el-button size="small" @click="auditStatusChange('2','tag3')" :class="statusTag=='tag3'?'acitved':''">审核通过</el-button>
                </el-badge>
                <el-badge :value="auditStatusRefuse" class="item">
                    <el-button size="small" @click="auditStatusChange('3','tag4')" :class="statusTag=='tag4'?'acitved':''">审核驳回</el-button>
                </el-badge>
        </div>
        <div style="padding-top: 10px;border-top: 10px solid #eee;border-bottom: 10px solid #eee;">
            <el-form :inline="true" :model="form" class="demo-form-inline">
                <div style="display:block;">
                    <el-form-item label="客户信息">
                        <el-input v-model="form.mobile" placeholder="手机号"></el-input>
                    </el-form-item>
                    <el-form-item label="所属城市">
                        <el-input v-model="form.city" placeholder="输入城市"></el-input>
                    </el-form-item>
                    <el-form-item label="审核时间">
                        <el-date-picker
                            v-model="form.audittime"
                            type="daterange"
                            align="right"
                            unlink-panels
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                            :picker-options="pickerOptions2">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item>
                        <el-button @click="resetForm();">重 置</el-button>
                        <el-button type="primary" @click="doQuery()">确 认</el-button>
                    </el-form-item>
                </div>
            </el-form>
        </div>
        <div style="margin-top:10px;">
            <el-button type="" @click="exportData()" icon="el-icon-download">导出</el-button>
            <el-button type="" @click="refreshData()" icon="el-icon-refresh">刷新</el-button>
        </div>
         <el-table
            ref="multipleTable"
            :data="list_data"
            border
            fit
            stripe
            v-loading="loading"
            style="width: 100%;margin-top: 10px;"
            @select="selectChange"
            @select-all="selectAllChange"
             @selection-change="handleSelectionChange">
            <el-table-column
                type="selection"
                align="center">
            </el-table-column>
            <el-table-column
                prop="id"
                label="编号"
                align="center">
            </el-table-column>
            <el-table-column
                prop="realname"
                label="客户姓名"
                align="center">
            </el-table-column>
            <el-table-column
                prop="mobile"
                align="center"
                label="手机号"
                width="120">
            </el-table-column>
            <el-table-column
                prop="money"
                align="center"
                :formatter="moneyFormatter"
                label="借款金额">
            </el-table-column>
            <el-table-column
                prop="term_loan"
                align="center"
                :formatter="termLoanFormatter"
                label="借款期限">
            </el-table-column>
            <el-table-column
                prop="city"
                align="center"
                label="所属城市">
            </el-table-column>
            <el-table-column
                prop="dt"
                align="center"
                label="时间"
                width="180">
            </el-table-column>
            <el-table-column
                prop="is_close"
                align="center"
                :formatter="accountStatusFormatter"
                label="账号状态">
            </el-table-column>
            <el-table-column
                prop="state_str"
                align="center"
                label="审核状态">
            </el-table-column>
            <el-table-column
                label="操作"
                align="center"
                width="130">
                <template slot-scope="scope">
                    <el-button type="text" size="small" v-if="adminAuth.indexOf('loan_info')>0" @click="viewInfo(scope.row)">查看</el-button>
                    <el-button type="text" size="small" v-if="adminAuth.indexOf('loan_edit')>0" @click="editInfo(scope.row)">编辑</el-button>
                    <el-button type="text" size="small" v-if="scope.row.state!=1&&adminAuth.indexOf('loan_show')>0" @click="audit(scope.row)">详情</el-button>
                    <el-button type="text" size="small" v-if="scope.row.state==1&&adminAuth.indexOf('loan_verify')>0" @click="audit(scope.row)">审核</el-button>
                </template>
            </el-table-column>
        </el-table>
        <div style="text-align:center;margin-top:20px;">
            <el-pagination
                background
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
                :current-page="currentPage"
                :page-size="pageSize"
                layout="total, prev, pager, next, jumper"
                :total="totalSize">
            </el-pagination>
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
    import { WEBAPI_ROOT } from '../../config'
    export default {
        data: function() {
            return {
                list_data:[],
                statusTag:'tag1',//条件标签
                auditStatusAll:'',
                auditStatusRefuse:'',
                auditStatusPass:'',
                auditStatusPre:'',
                form: {
                    check_verify:'',
                    mobile: '',
                    city: '',
                    audittime:'',
                },
                multipleSelection:[],
                pickerOptions2: {
                    shortcuts: [{
                        text: '最近一周',
                        onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                        picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '最近一个月',
                        onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                        picker.$emit('pick', [start, end]);
                        }
                    }, {
                        text: '最近三个月',
                        onClick(picker) {
                        const end = new Date();
                        const start = new Date();
                        start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                        picker.$emit('pick', [start, end]);
                        }
                    }]
                },
                adminAuth:'',
                loading:false,
                pageSize:10,//每页显示条数
                totalSize:0,//总条数
                currentPage:1,//当前页
            }
        },
        mounted: function() {
        },
        computed: {
            ...mapGetters(['getUserInfo', 'getLeftBig', 'getRightNavLinks', 'getNavMenu']),
        },
        methods: {
            ...mapActions(['removeUserInfo']),
            load_list(currentPage){
                this.loading=true;
                let audit_start_time=null,audit_end_time = null;
                let timeP = "yyyy-MM-dd hh:mm:ss";
                if(this.form.audittime && this.form.audittime.length==2){
                    audit_start_time = util.formatDate.format(this.form.audittime[0]);
                    audit_end_time = util.formatDate.format(this.form.audittime[1]);
                }
                let self=this;
                api.loan_list({
                    check_verify:self.form.check_verify,
                    mobile:self.form.mobile,
                    city:self.form.city,
                    audit_start_time:audit_start_time,
                    audit_end_time:audit_end_time,
                    page:currentPage
                })
                .then((data)=>{
                    setTimeout(()=>{
                        this.loading=false;
                    },500)
                    // console.log(JSON.stringify(data));
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        self.list_data=_data.data.list;
                        self.totalSize = _data.data.total;
                        self.currentPage = _data.data.page;
                        self.auditStatusAll = _data.data.data.total_num;
                        self.auditStatusPre = _data.data.data.total_doing;
                        self.auditStatusPass = _data.data.data.total_succ;
                        self.auditStatusRefuse = _data.data.data.total_fail;
                    }else{
                        this.$message.error({message:_data.message})
                    }
                })
                .catch((error)=>{
                    this.loading=false;
                    self.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            exportData(){//导出
                var ids = [];
                if(this.multipleSelection.length>0){
                    for(let i=0;i<this.multipleSelection.length;i++){
                        ids.push(this.multipleSelection[i].id);
                    }
                    var _ids = ids.join(",");
                    window.open(WEBAPI_ROOT+"/loan_export?person_loan_id="+_ids);
                    this.$refs.multipleTable.clearSelection();
                }else{
                    this.$message('请选择要导出的数据');
                }
            },
            refreshData(){//刷新
                this.load_list(1);
            },
            doQuery(){
                this.load_list(1);
            },
            resetForm() {
                this.form.mobile="";
                this.form.city="";
                this.form.agent_mobile="";
                this.form.regtime="";
                this.form.audittime="";
            },
            auditStatusChange(type,tag){
                this.form.check_verify = type;
                this.statusTag = tag;
                this.resetForm();
                this.load_list(1);
            },
            editInfo(row){//编辑信息
                this.$router.push({name:'loan_editSave', params: { id: row.id }});
            },
            viewInfo(row){//查看信息
                this.$router.push({name:'loan_info', params: { id: row.id }});
            },
            selectChange(selection,row){//单选
            },
            selectAllChange(selection){//全选
            },
            handleSelectionChange(selection) {
                this.multipleSelection = selection;
            },
            audit(row){//审核
                this.$router.push({name:'loan_audit', params: { id: row.id }});
            },
            isUeserClose(row){
                this.dialogVisible = true;
                this.id = row.id;
            },
            // showAudit(row){//查看审核信息
            //     this.$router.push({name:'loan_showAudit', params: { id: row.id }});
            // },
            handleSizeChange(val) {
                // console.log(`每页 ${val} 条`);
            },
            handleCurrentChange(val) {//改变页数
                // console.log(`当前页: ${val}`);
                this.load_list(val);
            },
            accountStatusFormatter(row, column) {
                if(row.is_close == 0){
                    return "启用"
                }else{
                    return "停用"
                }
            },
            moneyFormatter(row, column){
                return "¥"+row.money;
            },
            termLoanFormatter(row, column){
                return row.term_loan<12?row.term_loan+"个月":row.term_loan%12==0?row.term_loan/12+"年":Math.floor(row.term_loan/12)+"年"+row.term_loan%12+"个月";
            }
        },
        created: function() {
            this.adminAuth = this.getUserInfo.authority;
            this.load_list(this.currentPage);
        }
    }
</script>
<style>
</style>
