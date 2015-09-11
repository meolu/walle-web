Walle 瓦力 web ui
==========================

功能
---

Walle景愿是做一个web系统工具，实现最基础的上线部署，以及日志分析。

* 上线部署
	* 用户分身份注册、登录
	* 开发者发起上线任务申请
	* 管理者审核上线任务
	* 开发者一键部署上线
	* 查看上线日志

依赖
---

* git
* web ui的运行环境php、nginx（apache）、mysql
* composer，安装wall、yii2
* ssh

配置
---

WEBROOT/deploy.yml/env/production.yml
```
#production
scm:
  type: git
  url: git@github.com:meolu/walle.git
  branch: master
deployment:
  user: edison
  from: /var/www/deploy/from
  to: /var/www/deploy/to
  excludes:
  strategy: targz
releases:
  enabled: true
  max: 10
  symlink: current
  directory: releases
hosts:
    - 127.0.0.1
tasks:
  pre-deploy:
  on-deploy:
  post-release:
  post-deploy:
```


To Do List
----------

* 支持多项目部署
* 日志查询
* 日志图表展示
* 日志订阅
* 日志报警
