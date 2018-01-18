// 引入mockjs
const Mock = require('mockjs');
import { WEBAPI_ROOT } from '../config'

var Random = Mock.Random;
Mock.setup({
    timeout: 1000
})

Mock.mock(WEBAPI_ROOT+'/loan_list', {//客户列表
    "code": 0,
    "message": "成功",
    "data":{
        "list|10-22": [{
            'id|+1':1,
            'loanNo|1000-9999':2000,
            'loanName':Random.name(),
            'phoneNum':'18515288401',
            'loanMoney':Random.float(10000, 99999, 2, 2),
            'loanCycle':'1年2个月',
            'city':'上海',
            'accountStatus|0-1':0,
            'auditStatus|0-2':2,
            'createtime':Random.datetime()
        }],
        "auditStatusAll":2000,
        "auditStatusPre":200,
        "auditStatusPass":1000,
        "auditStatusRefuse":800,
        "pager": {
            "total": 30,
            "current": 1,
            "size": 10
        }
    }
})
.mock(WEBAPI_ROOT+'/loan_info', {//客户信息
    "code": 0,
    "message": "成功",
    "data":{
        "id|1-10":1
    }
})
.mock(WEBAPI_ROOT+'/user_close', {//账号停用
    "code": 0,
    "message": "成功",
    "data":{
        "id|1-10":1
    }
})
.mock(WEBAPI_ROOT+'/system_loginLog', {//登陆日志
    "code": 0,
    "message": "成功",
    "data|10-15":[{
        "time":Random.datetime(),
        "region":"北京海淀区",
        "ip":"122.243.123.343",
        "browser":"chrome"
    }]
})






