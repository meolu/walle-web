Walle 瓦力 web ui
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
* 线上文件指纹确认

依赖
---

* git
* web ui的运行环境php、nginx（apache）、mysql
* composer，安装walle、yii2
* ssh

安装
----
```
git clone git@github.com:meolu/walle-web.git
cd walle-web
vi config/web.php # 设置mysql连接
composer install
./yii migrate/up  # 导入数据库
```

快速开始
-------
* 首先[配置一个项目](https://github.com/meolu/walle-web/blob/master/yml-config.md)
* 提交上线任务
* 发起上线


To Do List
----------

* 部署出错详细信息优化
* 项目配置模块拆分
* 部署任务时的进度清单
* 项目绑定测试同学
* 部署耗时

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


### 有问题加群
**QQ：482939318**
