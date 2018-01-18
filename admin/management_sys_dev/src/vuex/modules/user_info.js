import { SET_USER_INFO, REMOVE_USER_INFO } from '../mutation-types'
var _userinfo = JSON.parse(localStorage.getItem('USERINFO') || '{}')
export default {
    state: {
        userinfo: _userinfo ? _userinfo : {}
    },
    getters: {
        getUserInfo: state => {
            return state.userinfo;
        },
        getToken: state => {
            if (state.userinfo.token)
                return state.userinfo.token;
            else
                return '';
        }
    },
    actions: {
        setUserInfo: ({ commit }, userinfo) => {
            commit(SET_USER_INFO, userinfo)
        },
        removeUserInfo: ({ commit }) => {
            commit(REMOVE_USER_INFO)
        }
    },
    mutations: {
        [SET_USER_INFO](state, userinfo) {
            state.userinfo = userinfo;
        },
        [REMOVE_USER_INFO](state, action) {
            state.userinfo = {};
        }
    }
}