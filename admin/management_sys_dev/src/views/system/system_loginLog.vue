<template>
    <el-table
        :data="list"
        border
        stripe
        style="width: 100%">
        <el-table-column
            prop="time"
            label="时间"
            align="center">
        </el-table-column>
        <el-table-column
            prop="region"
            label="地区"
            align="center">
        </el-table-column>
        <el-table-column
            prop="ip"
            label="IP"
            align="center">
        </el-table-column>
        <el-table-column
            prop="browser"
            label="浏览器"
            align="center">
        </el-table-column>
    </el-table>
</template>
<script>
    import api from '../../api'
    import {
        mapGetters,
        mapActions
    } from 'vuex'
    import Vali from '../../utils/validate_rules'
    import util from '../../utils/util'
    // import mock from '../../utils/mock'
    
    export default {
        data: function() {
            return {
                list:[]
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
                api.system_loginLog({})
                .then((data)=>{
                    // console.log(JSON.stringify(data))
                    var _data = util.formatJson(data);
                    if (_data.code == 0) {
                        self.list=_data.data;
                    }
                })
                .catch((error)=>{
                    self.$message.error({
                        message: '未知错误,请联系管理员！'
                    });

                })
            }
        },
        created: function() {
            this.load_list();
        }
    }
</script>
<style>
</style>
