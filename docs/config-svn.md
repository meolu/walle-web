Walle 瓦力 svn 部署配置、上线指南
==============================

svn部署上线与git有点不太一样，svn是推荐增量发布（当然也可以全量更新），在开发者提交文件列表（可能带版本号），管理员审核上线单。发起部署时，为该上线单开辟一个独立空间，检出代码，选择上线单中的文件（可能带版本号）同步到目标机群。有不了解宿主机和目标机群关系、上线流程的同学先到项目主页了解。

因为svn没有git的版本快照，所以在部署需要全量代码编译操作时，只能选择全量更新，此时要求发布的分支/tag/trunk是可发布状态。建议java + git组合。同理其它需要全量代码在宿主机做编译相关的操作的语言，且为svn版本管理，请慎用。

一、基本要求：
-----------

1. svn目录符合以下规范，详细可以注册[riouxsvn](https://riouxsvn.com)，作为svn测试地址。
    - branches
    - tags
    - trunk
2. 宿主机php进程用户www_php(假如，可通过配置的检测查看或ps aux|grep php)要加入目标机群部署用户www_remote(配置中)ssh-key信任，具体怎么添加可找sa或者百度或者[](https://github.com/meolu/walle-web/blob/master/docs/qa.md#如何添加用户ssh-key到目标机群部署用户ssh-key信任)，这一般是用户最不理解的地方，建议先花半小时理解linux用户概念和php配置。

二、配置项目
----------

配置主要三部分，只介绍前面两个，部分用户可能出错。

![](https://github.com/meolu/walle-web/blob/master/screenshots/base-svn.jpg)
![](https://github.com/meolu/walle-web/blob/master/screenshots/task.jpg)

三、提交上线单
------------
svn上线单有全量上线和增量上线两种主要形式，增量上线支持指定文件的版本号。分别看下可以有哪些格式填写上线单：

- 上线全量文件
    ```
    *
    ```
- 增量上线指定文件
    ```
    file_name1
    file_name2
    ```
- 增量上线指定文件的指定版本
    ```
    file_name1 commit_id
    file_name2 commit_id
    ```

![](https://github.com/meolu/walle-web/blob/master/screenshots/walle-svn-submit.jpg)
