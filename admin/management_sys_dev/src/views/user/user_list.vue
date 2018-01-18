<template>
    <div>
        <div style="padding-top: 10px;border-bottom: 10px solid #eee;">
            <el-form :inline="true" :model="form" class="demo-form-inline">
                <div style="display:block;">
                    <el-form-item label="客户信息">
                        <el-input v-model="form.mobile" placeholder="手机号"></el-input>
                    </el-form-item>
                    <el-form-item label="推荐人">
                        <el-input v-model="form.agent_mobile" placeholder="推荐人ID"></el-input>
                    </el-form-item>
                    <el-form-item label="注册时间">
                        <el-date-picker
                            v-model="form.regtime"
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
                        <el-button @click="reset();">重 置</el-button>
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
                prop="mobile"
                align="center"
                label="手机号">
            </el-table-column>
            <el-table-column
                prop="city"
                align="center"
                label="城市">
            </el-table-column>
            <el-table-column
                prop="dt"
                align="center"
                label="注册时间">
            </el-table-column>
            <el-table-column
                prop="is_close"
                align="center"
                :formatter="accountStatusFormatter"
                label="账号状态">
            </el-table-column>
            <el-table-column
                prop="agent_mobile"
                align="center"
                label="推荐人ID">
            </el-table-column>
            <el-table-column
                label="操作"
                align="center"
                width="180">
                <template slot-scope="scope">
                    <el-button type="text" size="small" v-if="adminAuth.indexOf('user_info')>0" @click="viewInfo(scope.row)">查看</el-button>
                    <el-button type="text" size="small" v-if="scope.row.is_close==0&&adminAuth.indexOf('user_edit')>0" @click="userClose(scope.row,1,'确认停用此账户？')">停用</el-button>
                    <el-button type="text" size="small" v-if="scope.row.is_close==1&&adminAuth.indexOf('user_edit')>0" @click="userClose(scope.row,0,'确认启用此账户？')">启用</el-button>
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
                form: {
                    mobile:'',
                    city:'',
                    agent_mobile:'',
                    regtime:''
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
                let start_time = null,end_time = null;
                let timeP = "yyyy-MM-dd hh:mm:ss";
                if(this.form.regtime && this.form.regtime.length==2){
                    start_time = util.formatDate.format(this.form.regtime[0]);
                    end_time = util.formatDate.format(this.form.regtime[1]);
                }
                let self=this;
                api.user_list({
                    mobile:self.form.mobile,
                    agent_mobile:self.form.agent_mobile,
                    city:self.form.city,
                    start_time:start_time,
                    end_time:end_time,
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
                        this.currentPage = _data.data.page;
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
                    window.open(WEBAPI_ROOT+"/user_export?user_id="+_ids);
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
            reset(){
                this.form.mobile="";
                this.form.agent_mobile="";
                this.form.regtime="";
            },
            editInfo(row){//编辑信息
                this.$router.push({name:'user_edit', params: { id: row.id }});
            },
            viewInfo(row){//查看信息
                this.$router.push({name:'user_info', params: { id: row.id }});
            },
            selectChange(selection,row){//单选
            },
            selectAllChange(selection){//全选
            },
            handleSelectionChange(selection) {
                this.multipleSelection = selection;
            },
            isUeserClose(row){
                this.dialogVisible = true;
                this.id = row.id;
            },
            userClose(row,status,msg){//账号停用
                this.$confirm(msg, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let self=this;
                    api.user_edit({
                        user_id:row.id,
                        is_close:status
                    })
                    .then((data)=>{
                        // console.log(JSON.stringify(data));
                        var _data = util.formatJson(data);
                        if (_data.code == 0) {
                            self.$message({message: '操作成功',type: 'success'});
                            for(let i=0;i<self.list_data.length;i++){
                                if(self.list_data[i].id == row.id){
                                    self.list_data[i].is_close = status;
                                    self.list_data.splice(i,1,self.list_data[i]);
                                }
                            }
                        }else{
                            this.$message.error(_data.message);
                        }
                    })
                    .catch((error)=>{
                        self.$message.error({
                            message: '未知错误,请联系管理员！'
                        });
                    })
                }).catch(() => {
                    console.log("cancel")       
                });
            },
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
