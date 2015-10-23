Walle 瓦力 git 部署配置指南
==========================

git部署是最推荐的方式，它无论对于何种语言都是合适的。

对此，walle有一些基本要求：

1. 宿主机php进程用户www_php(假如，可通过配置的检测查看或ps aux|grep php)的ssh-key要加入git/gitlab的deploy-keys
2. 宿主机php进程用户www_php(假如，可通过配置的检测查看或ps aux|grep php)要加入目标机群部署用户www_remote(配置中)ssh-key信任，具体怎么添加可找sa或者百度或者[](https://github.com/meolu/walle-web/blob/master/example/qa.md#如何添加用户ssh-key到目标机群部署用户ssh-key信任)，这一般是用户最不理解的地方，建议先花半小时理解linux用户概念和php配置。

配置主要三部分，只介绍前面两个，部分用户可能出错。

![](https://github.com/meolu/walle-web/blob/master/screenshots/base-git.jpg)
![](https://github.com/meolu/walle-web/blob/master/screenshots/task.jpg)

