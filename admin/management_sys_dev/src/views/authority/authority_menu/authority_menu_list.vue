<template>
    <div>
        <div class="admin_list_title">
             <el-button @click="showMenuAddOrEdit(null,0)"><i class='el-icon-plus'></i>添加主菜单</el-button>
        </div>
        <el-tree
            :data="list_data"
            :props="defaultProps"
            :show-checkbox="false"
            node-key="id"
            default-expand-all
            :expand-on-click-node="false"
            :render-content="renderContent">
        </el-tree>
        <el-dialog :title="dialogTitle" :visible.sync="dialogFormVisible" width='600px'>
            <el-form :rules="rules" ref="form" :model="form">
                <el-form-item prop="menu_name" label="名称" :label-width="formLabelWidth">
                <el-input v-model="form.menu_name" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item prop="menu_flag" label="标识" :label-width="formLabelWidth">
                <el-input v-model="form.menu_flag" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item prop="order_num" label="排序" :label-width="formLabelWidth">
                <el-input v-model="form.order_num" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item label="显示" :label-width="formLabelWidth">
                    <el-radio v-model="form.is_display" label="y">是</el-radio>
                    <el-radio v-model="form.is_display" label="n">否</el-radio>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="addOrEditData('form')">确 定</el-button>
            </div>
        </el-dialog>
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
                list_data:[],
                defaultProps: {
                    children: 'sub',
                    label: 'menu_name'
                },
                form: {
                    id:"",
                    menu_name: '',
                    menu_flag: '',
                    parent_id:'0',//父节点id
                    is_display:'y',//是否显示
                    order_num:'0'//菜单显示顺序
                },
                rules:{
                    menu_name: [
                        { required: true, message: '输入项不能为空', trigger: 'blur' }
                    ],
                    menu_flag: [
                        { required: true, message: '输入项不能为空', trigger: 'blur' }
                    ],
                    order_num: [
                        { required: true, message: '输入项不能为空', trigger: 'blur' }
                    ]
                },
                dialogTitle:'添加一级菜单',
                dialogFormVisible:false,
                formLabelWidth: '60px',
                curOperation:0 ,//0添加  1修改
            }
        },
        mounted: function() {
            
    
        },
        computed: {
            ...mapGetters(['getUserInfo', 'getLeftBig', 'getRightNavLinks', 'getNavMenu']),
        },
        methods: {
            ...mapActions(['removeUserInfo']),
            load_list(){
                let self=this;
                api.authority_menu_list({})
                .then((data)=>{
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        self.list_data=_data.data.menu_list;
                    }
                })
                .catch((error)=>{
                    self.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            showMenuAddOrEdit(data,flag){
                // console.log(JSON.stringify(this.list_data));
                this.dialogFormVisible = true;
                if(flag == 0){//添加
                    this.curOperation = 0;//更新当前窗口类型 0添加 1修改
                    this.form.menu_name = "";
                    this.form.order_num = "0";
                    this.form.is_display = "y";
                    if(data == null){//主菜单
                        this.dialogTitle = "添加一级菜单";
                        this.form.menu_flag = "";
                        this.form.parent_id = "0";
                    }else{
                        this.dialogTitle = "("+data.menu_name+")"+"添加子菜单";
                        this.form.menu_flag = data.flag+"_";
                        this.form.parent_id = data.id;
                    }
                }else if(flag == 1){//修改
                    this.dialogTitle = "("+data.menu_name+")"+"编辑菜单";
                    this.curOperation = 1;//更新当前窗口类型 0添加 1修改
                    this.form.id = data.id;
                    this.form.menu_name = data.menu_name;
                    this.form.menu_flag = data.flag;
                    this.form.is_display = data.is_display;
                    this.form.order_num = data.order_num;
                }
            },
            addOrEditData(formName){
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        // console.log(this.curOperation);
                        if(this.curOperation == 0){
                            this.addMenu();
                        }else if(this.curOperation == 1){
                            this.editSaveMenu();
                        }
                    } else {
                        return false;
                    }
                });
            },
            addMenu(){
                // console.log(this.form.menu_name+"_"+this.form.menu_flag+"_"+this.form.parent_id+"_"+this.form.is_display+"_"+this.form.order_num)
                this.dialogFormVisible = false;
                api.authority_menu_add({
                    menu_name:this.form.menu_name,
                    menu_flag:this.form.menu_flag,
                    parent_id:this.form.parent_id,
                    is_display:this.form.is_display,
                    order_num:this.form.order_num,
                })
                .then((data)=>{
                    // console.log(JSON.stringify(data))
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        this.$message({
                            message: '添加成功',
                            type: 'success'
                        });
                        this.load_list();
                    }else{
                         this.$message.error(_data.message);
                    }
                })
                .catch((error)=>{
                    this.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            isDelMenu(data) {
                this.$confirm('删除当前菜单, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.delMenu(data);
                }).catch(() => {
                    console.log("已取消删除");       
                });
            },
            delMenu(data){
                api.authority_menu_del({
                    id:data.id
                })
                .then((data)=>{
                    // console.log(data)
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        this.$message({
                            message: '删除成功',
                            type: 'success'
                        });
                        this.load_list();
                    }else{
                         this.$message.error(_data.message);
                    }
                })
                .catch((error)=>{
                    this.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            editSaveMenu(){
                // console.log(this.form.id+"_"+this.form.menu_name+"_"+this.form.menu_flag+"_"+this.form.is_display+"_"+this.form.order_num)
                this.dialogFormVisible = false;
                api.authority_menu_edit({
                    id:this.form.id,
                    menu_name:this.form.menu_name,
                    menu_flag:this.form.menu_flag,
                    is_display:this.form.is_display,
                    order_num:this.form.order_num,
                })
                .then((data)=>{
                    // console.log(JSON.stringify(data))
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        this.$message({
                            message: '修改成功',
                            type: 'success'
                        });
                        this.load_list();
                    }else{
                         this.$message.error(_data.message);
                    }
                })
                .catch((error)=>{
                    this.$message.error({
                        message: '未知错误,请联系管理员！'
                    });
                })
            },
            renderContent(h, { node, data, store }) {
                return (
                <span style="flex: 1; display: flex; align-items: center; justify-content: space-between; font-size: 14px; padding-right: 8px;">
                    <span>
                    <span>{node.label}</span>
                    </span>
                    <span>
                    <el-button style="font-size: 16px;" type="text" on-click={ () => this.showMenuAddOrEdit(data,0) }><i class='el-icon-plus'></i></el-button>
                    <el-button style="font-size: 16px;" type="text" on-click={ () => this.showMenuAddOrEdit(data,1) }><i class='el-icon-edit'></i></el-button>
                    <el-button style="font-size: 16px;" type="text" on-click={ () => this.isDelMenu(data) }><i class='el-icon-delete'></i></el-button>
                    </span>
                </span>);
            }

        },
        created: function() {
            this.load_list();
        }
    }
</script>
<style>
.admin_list_title{margin-bottom:10px;}
.el-tree-node__content{    
    border-bottom: 1px dashed #ccc;
    padding-top: 20px;
    padding-bottom: 20px;
}
</style>