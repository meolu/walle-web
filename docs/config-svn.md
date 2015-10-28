Walle 瓦力 svn 部署配置指南
==========================

svn部署上线与git有点不太一样，svn是属于增量发布，在开发者提交文件列表（可能带版本号），管理员审核上线单。发起部署时，为该上线单开辟一个独立空间，检出代码，选择上线单中的文件（可能带版本号）同步到目标机群。有不了解宿主机和目标机群关系、上线流程的同学先到项目主页了解。

同样，也是因为svn是属于增量发布，没有git的版本快照，所以在部署需要全量代码编译操作时，需要到目标机群完成。所以对于java + svn这种搭配，walle是不推荐的，建议java + git组合。同理其它需要全量代码在宿主机做编译相关的操作的语言，同时为svn版本管理，请慎用。

明白哪些语言与svn组合不适合后，如果你不在范围内，了解下walle的一些基本要求：

1. svn目录符合以下规范，详细可以注册[riouxsvn](https://riouxsvn.com)，作为svn测试地址。
    - branches
    - tags
    - trunk
2. 宿主机php进程用户www_php(假如，可通过配置的检测查看或ps aux|grep php)要加入目标机群部署用户www_remote(配置中)ssh-key信任，具体怎么添加可找sa或者百度或者[](https://github.com/meolu/walle-web/blob/master/docs/qa.md#如何添加用户ssh-key到目标机群部署用户ssh-key信任)，这一般是用户最不理解的地方，建议先花半小时理解linux用户概念和php配置。

配置主要三部分，只介绍前面两个，部分用户可能出错。

![](https://github.com/meolu/walle-web/blob/master/screenshots/base-svn.jpg)
![](https://github.com/meolu/walle-web/blob/master/screenshots/task.jpg)


配置完毕之后，先检测下，如无问题则可以发起上线单了：）
![](https://github.com/meolu/walle-web/blob/master/screenshots/detection.jpg)
