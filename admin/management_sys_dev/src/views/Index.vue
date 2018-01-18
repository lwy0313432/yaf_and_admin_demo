
 <template>
 <el-container class="main-box">
  <el-header>
      <el-col :span="24" >
            <el-col :span="12" style="font-size:26px;">
                <img style="width:40px;" src="https://caiyunupload.b0.upaiyun.com/newweb/imgs/logo_new.png" class="logo"><span style="padding-left:20px;color:#fff">米米多个贷平台<i style="color:#20a0ff"></i></span>
            </el-col>
           <el-col :span="12" style="text-align:right">
                <span class="admin-text"><i class="fa fa-user" aria-hidden="true" style="margin-right:5px;"></i>欢迎：{{getUserInfo.realname}}</span>
                <el-dropdown class="tip-logout" style="padding:0px;margin-left:20px;">
                    <span class="el-dropdown-link" style="color:#fff;">
                        设置<i class="el-icon-caret-bottom el-icon--right"></i>
                      </span>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item><span @click="update_pwd"><i class="fa fa-edit"></i> 修改密码</span></el-dropdown-item>
                        <el-dropdown-item><span @click="logout"><i class="fa fa-sign-out"></i> 退出</span></el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-col>
        </el-col>
  </el-header>
  <el-container>
    <el-aside width="200px">
        <el-menu :default-active="getNavMenu"  background-color="#545c64" text-color="#fff" active-text-color="#ffd04b" unique-opened router>
           <sys-menu :list_data="menu_list_data"></sys-menu>
        </el-menu>
    </el-aside>
    <el-main>
            <section >
                <el-col :span="24" style="margin-bottom:15px;">
                    <!-- <strong style="width:200px;float:left;color: #475669;">{{getLeftBig.title}}</strong> -->
                    <el-breadcrumb separator="/">
                        <el-breadcrumb-item :key="index" v-for="(item,index) in getNavLinks" :to="{ name: item.link }">
                            {{item.title}}
                        </el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
                <el-col :span="24" class="main-panel-box">
                    <transition name="fade">
                        <router-view></router-view>
                    </transition>
                </el-col>
            </section>
             <el-dialog title="修改密码" :visible.sync="dialogVisible" width="30%"  :close-on-click-modal="false">
                <el-form :model="userinfopwd" label-width="85px" :rules="rules" ref="userinfopwd">
                    <el-form-item label="旧密码" prop="old_passwd">
                        <el-input type="password" v-model="userinfopwd.old_passwd"></el-input>
                    </el-form-item>
                    <el-form-item label="新密码" prop="new_passwd">
                        <el-input type="password" v-model="userinfopwd.new_passwd"></el-input>
                    </el-form-item>
                    <el-form-item label="确认密码" prop="r_new_passwd">
                        <el-input type="password" v-model="userinfopwd.r_new_passwd"></el-input>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="dialogVisible = false">取 消</el-button>
                    <el-button @click="updatepwd_submit('userinfopwd')" type="primary">确定</el-button>
                </div>
            </el-dialog>
    </el-main>
  </el-container>
</el-container>

</template>
<script>
import api from "../api";
import { mapGetters, mapActions } from "vuex";
import Vali from "../utils/validate_rules";
import util from "../utils/util";

export default {
  data: function() {
    return {
      dialogVisible: false,
      menu_list_data:[],
      userinfopwd: {
        old_passwd: "",
        new_passwd: "",
        r_new_passwd: ""
      },
      rules: {
        old_passwd: [
          {
            required: true,
            message: "请输入密码"
          },
          {
            validator: Vali.password
          }
        ],
        new_passwd: [
          {
            required: true,
            message: "请输入密码"
          },
          {
            validator: Vali.password
          }
        ],
        r_new_passwd: [
          {
            required: true,
            message: "请输入密码"
          },
          {
            validator: Vali.password
          }
        ]
      }
    };
  },
  components:{
    sysMenu:{
      name:'sysMenu',
      template:`<div><el-submenu :index="value.id" :key="value.id" v-if="value.sub.length>0" v-for="(value,index) in list_data">
                <template slot="title" ><i class="el-icon-menu"></i>{{value.menu_name}}</template>
                <sys-menu :list_data="value.sub" v-if="value.sub.length>0"></sys-menu>
            </el-submenu><el-menu-item v-for="(value,index) in list_data" v-if="value.sub.length==0" :key="value.flag" :index="value.flag" :route="{name:value.flag}"><i class="el-icon-tickets"></i>{{value.menu_name}}</el-menu-item></div>`,
      props:['list_data'],
      data(){
        return {};
      },
      computed:{
        has_children(){
          return this.list_data.length>0?true:false;
        }
      }
    }
  },
  mounted: function() {},
  computed: {
    ...mapGetters([
      "getUserInfo",
      "getNavLinks",
      "getLeftBig",
      "getRightNavLinks",
      "getNavMenu"
    ])
  },
  methods: {
    ...mapActions(["removeUserInfo"]),
    logout: function() {
      var self = this;
      api.logout({})
      .then((res)=>{
        if(res.code==0){
          self.removeUserInfo();
          self.$router.push({name:'login'})
        }else{
          self.$message.error({
              message: '未知错误,请联系管理员！'
          });
        }
      })
    },
    update_pwd: function() {
      this.dialogVisible=true;
    },
    auth_menu(){
      let self=this;
      api.admin_my_menu({})
      .then((res)=>{
        if(res.code==0){
          self.menu_list_data=res.data.menu_list;
        }else{
          self.menu_list_data=[];
        }
      })
    },
    updatepwd_submit(formName) {
      this.$refs[formName].validate((valid) => {
          if (valid) {
              api.usercenter_changePasswd({
                  old_passwd:this.userinfopwd.old_passwd,
                  new_passwd:this.userinfopwd.new_passwd
              })
              .then((data)=>{
                  var _data = util.formatJson(data);
                  console.log(_data)
                  if (_data.code == 0) {
                      this.$message({
                          message: '修改成功',
                          type: 'success'
                      });
                      this.resetForm('userinfopwd');
                      this.dialogVisible = false;
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
    resetForm(formName){
        this.$refs[formName].resetFields();
    }
  },
  created: function() {
     var self = this;
      api.islogin({}).then((data) => {
          var _data = util.formatJson(data);
          if (_data.code == 0) {
              if(_data.data.isLogin=='yes'){
                //self.$router.push({name:'home'})
              }else{
                self.$router.push({name:'login'})
              }
          }else{
            self.$message.error({
              message: _data.message
          });
          }
      })
      .catch((error) => {
          self.$message.error({
              message: '未知错误,请联系管理员！'
          });
      });
    this.auth_menu();
  }
};
</script>