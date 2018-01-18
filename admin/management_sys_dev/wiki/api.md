1.客户列表
====

### 接口说明

##### 接口地址：/adminapi/loan_list

### 参数说明

|  参数  |  中文  |  类型  |  说明  |
| :----: | :----: | :----: | :----: |
| `name` | 客户信息 | String | init(未使用)/used（已使用）/overdue（已过期） |
| `city` | 所属城市 | String | init(未使用)/used（已使用）/overdue（已过期） |
| `recommendPerson` | 推荐人 | String | init(未使用)/used（已使用）/overdue（已过期） |
| `createtime` | 注册时间 | String | init(未使用)/used（已使用）/overdue（已过期） |
| `auditTime` | 审核时间 | String | init(未使用)/used（已使用）/overdue（已过期） |
| `auditStatus` | 审核状态 | String | init(未使用)/used（已使用）/overdue（已过期） |
| `page` | 第几页 | Number | 默认为1（第一页） |
| `size` | 个数/页 | Number | 每页显示10条数据 |

### 返回JSON示例

```JSON
{
    "code": 0,
    "message": "成功",
    "data": {
        "list": [
            {
                "id":1,
                "loanNo":8410,
                "loanName":"Sarah Johnson",
                "phoneNum":"18515288401",
                "loanMoney":36380.38,
                "loanCycle":"1年2个月",
                "city":"上海",
                "accountStatus":1,
                "auditStatus":1,
                "createtime":"1971-01-14 22:11:16"
            },{
                "id":1,
                "loanNo":8410,
                "loanName":"Sarah Johnson",
                "phoneNum":"18515288401",
                "loanMoney":36380.38,
                "loanCycle":"1年2个月",
                "city":"上海",
                "accountStatus":1,
                "auditStatus":1,
                "createtime":"1971-01-14 22:11:16"
            }
        ],
        "pager": {
            "total": 10,
            "current": 1,
            "size": 8
        }
    }
}
```

### 字段说明

|  字段  |  中文  |  类型  |  说明  |
| :----: | :----: | :----: | :----: |
| `id`            | 唯一标识      | Number | 加息券id |
| `loanNo`            | 编号      | Number | 加息券id |
| `loanName`         | 客户姓名      | Number | 100:1%（>=100）, 80:8‰（<100）|
| `phoneNum`     | 手机号      | String | 2017.09.13 13:28:11 |
| `loanMoney`   | 借款金额      | String | 2017.09.13 13:28:11 |
| `loanCycle` | 借款期限      | String | 2017.09.13 13:28:11 |
| `city`        | 所属城市    | String | 感恩有你1%加息券 |
| `accountStatus`   | 账号状态    | String | 出借网站期限≥300天的直投项目 |
| `auditStatus`      | 审核状态      | Number | 1:未使用, 0:已使用, -1:已过期 |
| `createtime` | 注册时间      | String | 2017.09.13 13:28:11 |
| `total`         | 总条数        | Number | 10:总页数为10页 |
| `current`       | 当前页        | Number | 1:当前页数为第1页 |
| `size`          | 个数/页       | Number | 8:每页显示8条数据 |















