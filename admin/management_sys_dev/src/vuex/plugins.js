const localStoragePlugin = store => {
    store.subscribe((mutation, { user_info }) => {
        localStorage.setItem('USERINFO', JSON.stringify(user_info.userinfo));
    })
}

export default [localStoragePlugin]