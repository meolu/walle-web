Walle 瓦力 web部署系统
==========================
[![Build Status](https://travis-ci.org/meolu/walle-web.svg?branch=master)](https://travis-ci.org/meolu/walle-web)

Walle 一个web部署系统工具，可能也是个持续发布工具，配置简单、功能完善、界面流畅、开箱即用！
支持各种web代码发布，静态的HTML，动态PHP，需要编译的JAVA等均支持。

[官网主页](http://www.huamanshu.com/walle.html)了解更多。[English Readme](https://github.com/meolu/walle-web/blob/master/README-en.md)，喜欢请为我标star吧：）

* 用户分身份注册、登录
* 开发者发起上线任务申请、部署
* 管理者审核上线任务
* 支持多项目部署
* 支持多项目多任务并行
* 快速回滚
* 项目的用户权限管理
* 部署前准备任务（前置检查）
* 代码检出后处理任务（如vendor，环境配置，java mvn、ant编译构建）
* 同步到各目标机器后收尾任务（如重启）
* 执行sql构建（不要担心忘记测试环境sql同步）
* 线上文件指纹确认


依赖
---

* bash(git、ssh)
* LNMP、LAMP(php5.4+)
* composer

安装
----
```
git clone git@github.com:meolu/walle-web.git
cd walle-web
vi config/web.php # 设置mysql连接
composer install  # 如果缺少bower-asset的话， 先安装：composer global require "fxp/composer-asset-plugin:*"
./yii migrate/up  # 导入数据库
```

快速开始
-------
* nginx/apache的webroot配置指向`walle-web/web`
* 配置邮箱（试用之后需要更改为自己的企业邮箱smtp，以免信息外泄）
    ```php
    vi config/params.php
    'support.email' => 'service@huamanshu.com', // 与config/web.php 中mail模块的username一致
    'mail-suffix'   => [                        // 允许注册的邮箱后缀
        'huamanshu.com',                        // 如果想用qq邮箱注册，请更改为qq.com
    ]

    vi config/web.php +25
    # 配置mail smtp模块
    'class'      => 'Swift_SmtpTransport',
    'host'       => 'ip or host',            # smtp 发件地址
    'username'   => 'service@huamanshu.com', # smtp 发件用户名
    'password'   => 'password',              # smtp 发件人的密码
    'port'       => 25,                      # smtp 端口
    'encryption' => 'tls',                   # smtp 协议
    ```
* 注册一个管理员身份用户(已有`admin/admin`)，配置一个项目
* 有公司邮箱的开发者注册(已有`demo/demo`)，提交上线任务
* 管理员审核上线任务
* 开发者发起上线


To Do List
----------
- 项目管理员审核
- 配置检测
    - 宿主机用户是否有git权限
    - 宿主机用户是否能免密码登录目标机器
    - 宿主机用户是否有目录权限
    - 目标机用户是否有目录权限
- Travis CI 集成
- 静态资源管理器
- 邮件提醒：可配置提醒事件
- 灰度发布：指定机器发布

持续更新开启更多功能
-----------------
```
git pull
./yii migrate    # 更新数据库
```

截图
---

#### 配置管理
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-config-edit.jpg)

#### 注册发普通开发者和管理角色
![](https://raw.github.com/meolu/walle-web/master/screenshots/login.png)

#### 提交上线任务
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-submit.jpg)

#### 上线列表
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-dev-list.jpg)

#### 上线流程图
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-flow.png)

#### 演示
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle.gif)

## CHANGELOG
瓦力的版本记录：[CHANGELOG](https://github.com/meolu/walle-web/blob/master/CHANGELOG.md)


交流讨论
-------
- [常见问题及解决办法手册](https://github.com/meolu/walle-web/blob/master/qa.md)
- [submit issue](https://github.com/meolu/walle-web/issues/new)
- QQ（有问必答）: 482939318
