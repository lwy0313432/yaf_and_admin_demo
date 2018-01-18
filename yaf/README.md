# 管理后台的sql
1.表结构
```


CREATE TABLE `admin` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password` char(32) NOT NULL,
        `realname` varchar(50) NOT NULL COMMENT '真实姓名',
        `role` enum('service','editor','operator','manager','super','risk','design','devel','marketing','product','accounting') NOT NULL,
        `is_del` tinyint(1) unsigned NOT NULL,
        `authority` text COMMENT '存储menu表的flag，多个flag之间以逗号隔开',
        PRIMARY KEY (`id`),
        UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB   DEFAULT CHARSET=utf8;

CREATE TABLE `admin_menu` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `menu_name` varchar(256) NOT NULL COMMENT '菜单名称',
        `flag` varchar(255) DEFAULT NULL COMMENT '菜单的唯一标示，包含了父菜单的flag，用_分割',
        `is_display` enum('n','y') DEFAULT 'y' COMMENT '该菜单项是否是需要展示的菜单项。是否需要展示在页面上。某些异步请求不需要展示在页面上。',
        `parent_flag` varchar(256) DEFAULT NULL COMMENT '父菜单的flag',
        `parent_id` int(10) unsigned DEFAULT '0' COMMENT '父菜单的id',
        `order_num` int(10) unsigned DEFAULT '0' COMMENT '同级菜单的排列顺序',
        `level` enum('1','2','3','4','5') DEFAULT '1' COMMENT '菜单的级别，默认是1级菜单，最多五级菜单',
        `dt` datetime DEFAULT NULL,
        `is_del` enum('y','n') DEFAULT 'n' COMMENT '该菜单是否删除',
        `del_dt` datetime DEFAULT NULL COMMENT '删除时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `flag` (`flag`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

```
2.菜单数据导入
```
INSERT INTO `admin_menu` VALUES (4,'权限管理','authority','y','',0,9000,'1','2015-06-18 13:49:32','n','2018-01-18 00:00:00'),(5,'菜单 管理','authority_menu','y','authority',4,0,'2','2015-06-18 13:49:28','n','2018-01-18 00:00:00'),(6,'菜单列表','authority_menu_list','y','authority_menu',5,0,'3','2018-01-18 00:00:00','n','2018-01-18 00:00:00'),(7,'添加菜单','authority_menu_add','n','authority_menu',5,0,'3','2015-06-18 13:52:21','n','2018-01-18 00:00:00'),(11,'删除菜单','authority_menu_del','n','authority_menu',5,0,'3','2018-01-18 00:00:00','n','2018-01-18 00:00:00'),(12,'编辑菜单','authority_menu_edit','n','authority_menu',5,0,'3','2018-01-18 00:00:00','n','2018-01-18 00:00:00'),(13,'管理员','authority_admin','y','authority',4,0,'2','2015-06-18 13:47:31','n','2018-01-18 00:00:00'),(15,'管理员列表','authority_admin_list','y','authority_admin',13,0,'3','2018-01-18 00:00:00','n','2018-01-18 00:00:00'),(16,'添加管理员','authority_admin_add','y','authority_admin',13,0,'3','2018-01-18 00:00:00','n','2018-01-18 00:00:00'),(17,'删除管理员','authority_admin_del','n','authority_admin',13,0,'3','2018-01-18 00:00:00','n','2018-01-18 00:00:00'),(71,'个人中心','usercenter','y','',0,8000,'1','2015-06-24 18:25:53','n','2018-01-18 00:00:00'),(72,'修改密码','usercenter_changePasswd','y','usercenter',71,0,'2','2015-06-24 18:26:41','n','2018-01-18 00:00:00'),(73,'查看管理员','authority_admin_getinfo','n','authority_admin',13,0,'3','2015-06-25 10:49:30','n','2018-01-18 00:00:00'),(94,'编辑管理员','authority_admin_update','n','authority_admin',13,0,'3','2015-07-07 15:13:18','n','2018-01-18 00:00:00');
```
