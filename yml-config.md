# 配置
-----

## 配置project示例
--------------

deploy.yml/env/conf_tpl.yml
```
#production
scm:
  url: git@github.com:meolu/walle-web.git
  branch: development
deployment:
  user: www
  from: /data/www/walle-deploy
  env: test
  excludes:
    - READ.ME
  strategy: targz
releases:
  user: www
  destination: /data/www/walle
  release: /data/www/walle-deploy/releases
hosts:
    - 127.0.0.1
tasks:
  pre-deploy:
  	- test -d {WORKSPACE}
  post-deploy:
    - cp -rf {WORKSPACE}/web/index-test.php {WORKSPACE}/web/index.php
    - cp -rf /data/www/yii2_test/vendor {WORKSPACE}/
    - cp -rf {WORKSPACE}/yii-test {WORKSPACE}/yii
  post-release:
  	- /usr/local/nginx/sbin/nginx -s reload
```

## 参数
-------

### scm
-------
第一阶段：版本管理信息
- **url** git的ssh地址，可以添加部署机器的id_rsa.pub到git的ssh-key列表里
- **branch** 选择要部署的分支，测试环境推荐development(master)，生产环境master(online)

### deployment
--------------
第二阶段：**部署**，配置我们如何从git中检出代码拷贝到一个临时目录
- **user** 我们在部署宿主机操作git，copy等操作的用户名
- **from** 代码从git检出存放的目录，这个目录是带.git信息
- **env** 支持多个项目，同时一个项目有多个环境（测试，仿真，生产），所以最后与**from**、git地址拼成一个项目环境地址`{from}/{env}/{git-repo}`，如`/data/www/walle/test/walle-web`
- **excludes** 在rsync时要排除的文件，如.git，log文件夹，unnits

### releases
------------
第三阶段：**目标机器**
- **user** 我们在部署目录机器操作copy，rsync等操作的用户名
- **destination** 代码最终地址，直接提供服务的地址，也就是nginx的root地址
- **release** 发布的版本库，在release目录里会有各个发布的版本，作为软链的文件源，可以快速更改软链接从而回滚历史版本

### hosts
---------
第四阶段：要部署到哪些机器，但需要注意的是这些机器必须是可以ssh登录的，并且ssh免密码登录

### tasks
---------
- **pre-deploy** 在部署代码之前的准备工作，如git的一些前置检查、vendor的安装（更新）
- **post-deploy** git代码检出之后，可能做一些调整处理，如vendor拷贝，配置环境适配（mv config-test.php config.php）
- **post-release** 所有目标机器都部署完毕之后，做一些清理工作，如删除缓存、重启服务（nginx、php、task）

