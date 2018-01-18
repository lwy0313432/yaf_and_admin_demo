import { SET_NAV_LINKS, REMOVE_NAV_LINKS, SET_NAV_MENU } from '../mutation-types'
export default {
    state: {
        nav_links: [],
        nav_menu: ''
    },
    getters: {
        getNavLinks: state => {
            return state.nav_links;
        },
        getNavMenu: state => {
            return state.nav_menu;
        },
        getRightNavLinks: state => {

            if (state.nav_links.length >= 2) {
                return state.nav_links.slice(0, state.nav_links.length - 1);
            } else {
                return state.nav_links;
            }

        },
        getLeftBig: state => {

            if (state.nav_links.length >= 2) {
                return state.nav_links.slice(-1)[0];
            } else {
                return {title:''};
            }
        }
    },
    actions: {
        setRightNavLinks: ({ commit }, nav_links) => {
            commit(SET_NAV_LINKS, nav_links)
        },
        setNavMenu: ({ commit }, nav_menu) => {
            commit(SET_NAV_MENU, nav_menu)
        }
    },
    mutations: {
        [SET_NAV_LINKS](state, nav_links) {

            state.nav_links = nav_links;
        },
        [SET_NAV_MENU](state, nav_menu) {

            state.nav_menu = nav_menu;
        }
    }
}