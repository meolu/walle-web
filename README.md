Walle 瓦力 web部署系统
==========================

功能
---

Walle景愿是做一个web部署系统工具，[官网主页](http://www.huamanshu.com/walle.html)了解更多。

* 用户分身份注册、登录
* 开发者发起上线任务申请
* 管理者审核上线任务
* 支持多项目部署
* 开发者一键部署上线
* 快速回滚
* 查看上线日志
* 部署前准备任务（前置检查）
* 代码检出后处理任务（如vendor，环境配置）
* 同步到各目标机器后收尾任务（如重启）
* 执行sql构建（不要担心忘记测试环境sql同步）
* 线上文件指纹确认

依赖
---

* git
* web ui的运行环境php5.4、nginx（apache）、mysql
* composer，安装walle、yii2
* ssh

安装
----
```
git clone git@github.com:meolu/walle-web.git
cd walle-web
vi config/web.php # 设置mysql连接
composer install  # 如果缺少bower-asset的话， 先安装：composer install global require "fxp/composer-asset-plugin:*"
./yii migrate/up  # 导入数据库
```

快速开始
-------
* 首先，配置邮箱，如果没有，好吧，先忽略，注册完手动修改user表的is_email_verified=1即可登录
    ```php
    vi config/params.php
    'mail-suffix' => [
        '公司邮箱后缀.com',
    ]

    vi config/web.php +25
    # 配置mail smtp模块
    'class'      => 'Swift_SmtpTransport',
    'host'       => 'ip or host',           # smtp 发件地址
    'username'   => 'admin@huamanshu.com',  # smtp 发件用户名
    'password'   => 'password',             # smtp 发件人的密码
    'port'       => 25,                     # smtp 端口
    'encryption' => 'tls',                  # smtp 协议
    ```
* 注册一个管理员身份用户(已有`admin/admin`)，配置一个项目
* 有公司邮箱的开发者注册(已有`demo/demo`)，提交上线任务
* 管理员审核上线任务
* 开发者发起上线


To Do List
----------

* 部署出错详细信息优化
* 项目的开发同学分组可见权限

持续更新开启更多功能
-----------------
```
git pull
composer update
./yii migrate    # 更新数据库
```

截图
---
#### 注册发普通开发者和管理角色
![](https://raw.github.com/meolu/walle-web/master/screenshots/login.png)

#### 提交上线任务
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle.gif)

#### 上线列表
![](https://raw.github.com/meolu/walle-web/master/screenshots/task-list.png)

#### 发起上线
![](https://raw.github.com/meolu/walle-web/master/screenshots/deploy.png)

## CHANGELOG
瓦力的版本记录：[CHANGELOG](https://github.com/meolu/walle-web/blob/master/CHANGELOG.md)


## 交流群（有问必答）
**QQ：482939318**
