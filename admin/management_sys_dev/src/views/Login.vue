<template>
  <div class="login-container">
    <el-form autoComplete="on" :model="loginForm" :rules="loginRules" ref="loginForm" label-position="left" label-width="0px"
      class="card-box login-form">
      <h3 class="title">防伪管理平台</h3>
      <el-form-item prop="username">
        <el-input
          placeholder="用户名"
          suffix-icon="fa fa-user"
          v-model="loginForm.username" />
       
      </el-form-item>
      <el-form-item prop="password">
        <el-input
         type="password"
          placeholder="密码"
          suffix-icon="fa fa-key"
          v-model="loginForm.password" />
       
      </el-form-item>
      <el-form-item prop="vcode">
        
        <el-input placeholder="请输入验证码" v-model="loginForm.vcode">
          <template slot="append">
            <img :src="vcode_url" alt="" @click="change_vcode">
          </template>
        </el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" style="width:100%;" :loading="loading" @click.native.prevent="handleLogin">
          登   录
        </el-button>
      </el-form-item>
      
    </el-form>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import api from "../api";
import util from "../utils/util";
import { WEBDDOMAIN_ROOT } from '../config'
export default {
  name: "login",
  data() {
    const validateUsername = (rule, value, callback) => {
      if (!/^[0-9A-Za-z_.@-]{2,25}$/.test(value)) {
        callback(new Error("格式错误，2到25位字符"));
      } else {
        callback();
      }
    };
    const validatePass = (rule, value, callback) => {
      if (
        !/^[0-9a-zA-Z\~\!\@\#\$\%\^\&\*\(\)\_\-\+\=\{\}\[\]\;\'\:\"\<\,\>\.\?\/]{6,20}$/.test(
          value
        )
      ) {
        callback(new Error("格式错误，包含数字，大小写字母，特殊字符6到20位字符"));
      } else {
        callback();
      }
    };
    return {
      loginForm: {
        username: "",
        password: "",
        vcode: ""
      },
      loginRules: {
        username: [
          { required: true, trigger: "blur", validator: validateUsername }
        ],
        password: [
          { required: true, trigger: "blur", validator: validatePass }
        ],
        vcode: [{ required: true, trigger: "blur",message:"请输入验证码" }]
      },
      loading: false,
      origin_vcode_url:WEBDDOMAIN_ROOT+'vcode',
      vcode_url:WEBDDOMAIN_ROOT+'vcode'
    };
  },
  created: function() {
    var self = this;
    api
      .islogin({})
      .then(data => {
        var _data = util.formatJson(data);
        if (_data.code == 0) {
          if (_data.data.isLogin == "yes") {
             self.$router.push({name:'home'})
          }
        }
      })
      .catch(error => {
        self.$message.error({
          message: "未知错误,请联系管理员！"
        });
      });
  },
  methods: {
    ...mapActions(["setUserInfo"]),
    change_vcode(){
      let self=this;
      self.vcode_url=self.origin_vcode_url+"?rd="+new Date().getTime();
    },
    handleLogin() {
      var self = this;

      self.$refs.loginForm.validate(valid => {
        if (valid) {
          this.loading = true;
          api
            .dologin({
              username: self.loginForm.username,
              password: self.loginForm.password,
              vcode: self.loginForm.vcode
            })
            .then(data => {
              this.loading = false;
              var _data = util.formatJson(data);
              if (_data.code == 0) {
                self.setUserInfo(_data.data.admin_info);
                self.$router.push({ name: "home" });
              } else {
                this.loading = false;
                if(_data.code=400010){
                  self.vcode_url=self.origin_vcode_url+"?rd="+new Date().getTime();
                }
                self.$message.error({
                  message: _data.message
                });
              }
            })
            .catch(error => {
              this.loading = false;
              self.$message.error({
                message: "未知错误,请联系管理员！"
              });
            });
        }
      });
    }
  }
};
</script>

<style rel="stylesheet/scss" lang="scss">
@mixin clearfix {
  &:after {
    content: "";
    display: table;
    clear: both;
  }
}

@mixin scrollBar {
  &::-webkit-scrollbar-track-piece {
    background: #d3dce6;
  }
  &::-webkit-scrollbar {
    width: 6px;
  }
  &::-webkit-scrollbar-thumb {
    background: #99a9bf;
    border-radius: 20px;
  }
}

@mixin relative {
  position: relative;
  width: 100%;
  height: 100%;
}
$bg: #2d3a4b;
$dark_gray: #889aa4;
$light_gray: #eee;
.login-container {
  @include relative;
  height: 100vh;
  background-color: $bg;
  input:-webkit-autofill {
    -webkit-box-shadow: 0 0 0px 1000px #293444 inset !important;
    -webkit-text-fill-color: #fff !important;
  }
  input {
    background: transparent;
    border: 0px;
    -webkit-appearance: none;
    border-radius: 0px;
    padding: 12px 5px 12px 15px;
    color: $light_gray;
    height: 47px;
  }

  .login-form .el-input-group__append {
    padding: 0;
  }
  .login-form .el-input-group__append > img {
    height: 47px;
    display:block;
    cursor:pointer;
  }

  .login-form {
    position: absolute;
    left: 0;
    right: 0;
    width: 400px;
    padding: 35px 35px 15px 35px;
    margin: 120px auto;
  }
 .title {
    font-size: 26px;
    font-weight: 400;
    color: #eee;
    margin: 0px auto 40px auto;
    text-align: center;
    font-weight: bold;
}
  .el-form-item {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    color: #454545;
  }
}
</style>
