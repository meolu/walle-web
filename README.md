Walle 瓦力 web ui
==========================

功能
---

Walle景愿是做一个web系统工具，实现最基础的上线部署，以及日志分析（后续可能拆分出来）。

* 上线部署
	* 用户分身份注册、登录
	* 开发者发起上线任务申请
	* 管理者审核上线任务
	* 开发者一键部署上线
  * 快速回滚
	* 查看上线日志

依赖
---

* git
* web ui的运行环境php、nginx（apache）、mysql
* composer，安装walle、yii2
* ssh

安装
----
```
>composer install
```

配置project示例
-------

deploy.yml/env/project_name.yml
```
#production
scm:
  type: git
  url: git@github.com:meolu/walden.git
  branch: master
deployment:
  user: paopao 
  from: /var/www/deploy/from
  env: simulate
  excludes:
    - READ.ME
  strategy: targz
releases:
  user: paopao
  max: 10
  destination: /var/www/walden
  release: /var/www/deploy/to/releases
hosts:
    - 127.0.0.1
tasks:
  pre-deploy:
  on-deploy:
  post-release:
    - test -d {WORKSPACE}/vendor && cp -rf {WORKSPACE}/vendor {VERSION}/ || echo 'no vendor'
  post-deploy:                                                                                                                                    
```


To Do List
----------

* 支持多项目部署
* 日志查询
* 日志图表展示
* 日志订阅
* 日志报警

截图
---
#### 提交上线任务
![](https://raw.github.com/meolu/walle-web/master/screenshots/submit.gif)

#### 上线列表
![](https://raw.github.com/meolu/walle-web/master/screenshots/task-list.png)

#### 发起上线
![](https://raw.github.com/meolu/walle-web/master/screenshots/deploy.png)
