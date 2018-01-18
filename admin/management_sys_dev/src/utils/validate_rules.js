export default {
    username(rule, value, callback) {
        if (!/^[0-9A-Za-z_.@-]{2,25}$/.test(value)) {
            callback(new Error('格式错误，2到25位字符'));
        } else {
            callback();
        }
    },
    password(rule, value, callback) {
        if (!/^[0-9a-zA-Z\~\!\@\#\$\%\^\&\*\(\)\_\-\+\=\{\}\[\]\;\'\:\"\<\,\>\.\?\/]{6,20}$/.test(value)) {
            callback(new Error('格式错误，包含数字，大小写字母，特殊字符6到20位字符'));
        } else {
            callback();
        }
    }
}