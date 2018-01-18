<template>
    <div>
        欢迎进入防伪管理平台！
    </div>
</template>
<script>
    import api from '../api'
    import {
        mapGetters,
        mapActions
    } from 'vuex'
    import Vali from '../utils/validate_rules'
    import util from '../utils/util'
    
    export default {
        data: function() {
            return {
                
            }
        },
        mounted: function() {
        },
        computed: {
            ...mapGetters(['getUserInfo', 'getLeftBig', 'getRightNavLinks', 'getNavMenu']),
        },
        methods: {
            ...mapActions(['removeUserInfo']),
            
    
    
        },
        created: function() {
    
        }
    }
</script>