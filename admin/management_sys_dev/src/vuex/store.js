import Vue from 'vue'
import Vuex from 'vuex'
import user_info from './modules/user_info'
import nav_info from './modules/nav_info'
import plugins from './plugins'
Vue.use(Vuex)
export default new Vuex.Store({
    modules: {
        user_info,
        nav_info
    },
    plugins
})