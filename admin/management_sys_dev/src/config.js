

export const WEBDDOMAIN_ROOT = (function() {
    let url = '';
    switch (process.env.NODE_ENV) {
        case "production":
            url = '/';
        case "production_test":
        case "development":
            url = 'http://34idea.com/';
            break;
        default:
            break;
    }
    return url;
})();

export const WEBAPI_ROOT = (function() {
    let url = '';
    switch (process.env.NODE_ENV) {
        case "production":
            url = '/adminapi';
        case "production_test":
        case "development":
            url = 'http://34idea.com/adminapi';
            break;
        default:
            break;
    }
    return url;
})();

export const WEBPAYAPI_ROOT = (function() {
    let url = '';
    switch (process.env.NODE_ENV) {
        case "production":
            url = 'https://pay.yindou.com';
            break;
        case "production_test":
        case "development":
            url = 'http://sitpay.yind123.com';
            break;
        default:
            break;
    }
    return url;
})();
