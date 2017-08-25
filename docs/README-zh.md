![](https://raw.github.com/meolu/walle-web/master/docs/logo.jpg)

瓦力 - 部署系统
==========================
[![Build Status](https://travis-ci.org/meolu/walle-web.svg?branch=master)](https://travis-ci.org/meolu/walle-web)
[![Packagist](https://img.shields.io/packagist/v/meolu/walle-web.svg)](https://packagist.org/packages/meolu/walle-web)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Walle 一个web部署系统工具，可能也是个持续发布工具，配置简单、功能完善、界面流畅、开箱即用！支持git、svn版本管理，支持各种web代码发布，静态的HTML，动态PHP，需要编译的JAVA等。

[官网主页](https://www.walle-web.io) | [文档手册](https://www.walle-web.io/docs) | [English Readme](https://github.com/meolu/walle-web/blob/master/README.md).

目前，超过百家企业生产环境部署使用，欢迎star、fork、试用 ：）

* 支持git、svn版本管理
* 用户分身份注册、登录
* 开发者发起上线任务申请、部署
* 管理者审核上线任务
* 支持多项目部署
* 支持多项目多任务并行
* 快速回滚
* 项目的用户权限管理
* 部署前准备任务pre-deploy（前置检查）
* 代码检出后处理任务post-deploy（如vendor）
* 同步后更新软链前置任务pre-release
* 发布完毕后收尾任务post-release（如重启）
* 线上文件指纹确认
* 多机器并发传输文件(Ansible)



依赖
---

* Bash(git、ssh)
* LNMP/LAMP(php5.4+)
* Composer
* Ansible(可选)

安装
----
```
git clone git@github.com:meolu/walle-web.git
cd walle-web
vi config/local.php # 设置mysql连接
composer install  # 如果缺少bower-asset的话， 先安装：composer global require "fxp/composer-asset-plugin:*"
./yii walle/setup # 初始化项目
配置nginx/apache的webroot指向walle-web/web，简单范例详见页面底部常见问题和解决办法。
```

如有需要，移步[最最最详细安装指南](https://walle-web.io/docs/installation.html)


快速开始
-------
* 注册一个管理员身份用户(已有`admin/admin`)，配置一个项目。
    * [git配置范例](https://walle-web.io/docs/git-configuration.html)
    * [svn配置范例](https://walle-web.io/docs/svn-configuration.html)
* 开发者注册用户(已有`demo/demo`)，提交上线单
* 管理员审核上线单
* 开发者发起上线


To Do List
----------
- Travis CI 集成
- 邮件提醒：可配置提醒事件
- 灰度发布：指定机器发布
- 引入websocket
- 静态资源管理器
- 自定义公司logo
- 自定义变量
- 支持国际化：增加英文语言
- 支持Docker
- 开放接口

持续更新开启更多功能
-----------------
```
./yii walle/upgrade    # 升级walle
```

截图
---

#### 配置管理
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/zh-cn/static/walle-config-edit.jpg)

#### 提交上线任务
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/zh-cn/static/walle-submit.jpg)

#### 上线列表
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/zh-cn/static/walle-dev-list.jpg)

#### 宿主机、目标机群、操作用户关系
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/zh-cn/static/walle-flow-relation.jpg)

#### 上线流程图
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/zh-cn/static/walle-flow.png)

#### 演示
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/zh-cn/static/walle.gif)

## CHANGE LOG
瓦力的版本记录：[CHANGELOG](https://github.com/meolu/walle-web/releases)


交流讨论
-------
- [常见问题及解决办法手册](https://walle-web.io/docs/troubleshooting.html)
- [submit issue](https://github.com/meolu/walle-web/issues/new)
- [官方文档手册](https://walle-web.io/docs)
- qq群：482939318

勾搭下
--------
<img src="https://raw.githubusercontent.com/meolu/walle-web/feature-weixin/docs/weixin.wushuiyong.jpg" width="244" height="314" alt="吴水永微信" align=left />
