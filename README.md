Walle 瓦力 web部署系统
==========================
[![Build Status](https://travis-ci.org/meolu/walle-web.svg?branch=master)](https://travis-ci.org/meolu/walle-web)

Walle 一个web部署系统工具，可能也是个持续发布工具，配置简单、功能完善、界面流畅、开箱即用！

支持git、svn版本管理，支持各种web代码发布，静态的HTML，动态PHP，需要编译的JAVA等。

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
* 支持git、svn版本管理


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
配置nginx/apache的webroot指向walle-web/web，简单范例详见页面底部常见问题和解决办法。
```

快速开始
-------
* 注册一个管理员身份用户(已有`admin/admin`)，配置一个项目
* 开发者注册用户(已有`demo/demo`)，提交上线单
* 管理员审核上线单
* 开发者发起上线

高级自定义
--------
此时你可能考虑要作为一个公司内部新项目的试用版本，那么你将需要做以下的处理，更适合业务需求。

* 配置允许注册的邮箱后缀  
    ```php
    vi config/params.php
    'mail-suffix'   => [  // 允许注册的邮箱后缀，一般为公司邮箱后缀，可多个
        'huamanshu.com',  // 如：只允许花满树邮箱注册
    ]
    ```

* 配置企业邮箱smtp
    ```php
    vi config/web.php +25
    # 配置mail smtp模块
    'class'      => 'Swift_SmtpTransport',
    'host'       => 'smtp.huamanshu.com',    # smtp 发件地址
    'username'   => 'service@huamanshu.com', # smtp 发件用户名
    'password'   => 'password',              # smtp 发件人的密码
    'port'       => 25,                      # smtp 端口
    'encryption' => 'tls',                   # smtp 协议

    vi config/params.php
    'support.email' => 'service@huamanshu.com', // 与config/web.php 中mail模块的username一致
    ```

To Do List
----------
- Travis CI 集成
- 静态资源管理器
- 邮件提醒：可配置提醒事件
- 灰度发布：指定机器发布
- 引入websocket
- 自定义公司logo
- 自定义变量

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

#### 提交上线任务
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-submit.jpg)

#### 上线列表
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-dev-list.jpg)

#### 宿主机、目标机群、操作用户关系
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-work.png)

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
