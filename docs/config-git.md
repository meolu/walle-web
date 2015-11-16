Walle 瓦力 git 部署配置指南
==========================

git部署是最推荐的方式，它无论对于何种语言都是合适的。


一、基本要求：
-----------

1. 宿主机php进程用户www_php(假如，可通过配置的检测查看或ps aux|grep php)的ssh-key要加入git/gitlab的deploy-keys。当然也可以http地址带用户名密码（不推荐）：https://username:password@github.com/meolu/walle-web.git
2. 宿主机php进程用户www_php(假如，可通过配置的检测查看或ps aux|grep php)要加入目标机群部署用户www_remote(配置中)ssh-key信任，具体怎么添加可找sa或者百度或者[](https://github.com/meolu/walle-web/blob/master/docs/faq.md#如何添加用户ssh-key到目标机群部署用户ssh-key信任)，这一般是用户最不理解的地方，建议先花半小时理解linux用户概念和php配置。


二、配置项目
----------

![](https://github.com/meolu/walle-web/blob/master/screenshots/base-git.jpg)
![](https://github.com/meolu/walle-web/blob/master/screenshots/task.jpg)

三、检测项目配置
-------------

配置完毕之后，先检测下，如无问题则可以发起上线单了：）
![](https://github.com/meolu/walle-web/blob/master/screenshots/detection.png)

四、检测的错误和解决办法
--------------------
- 宿主机代码检出检测出错，请确认php进程用户{user}有代码存储仓库{path}读写权限，且把ssh-key加入git的deploy-keys列表。详细错误：{error}

    - 问题：**请确认php进程用户{user}有代码存储仓库{path}读写权限**

        ```
        没有权限，是因为用户{user}对目录{path}没有读写权限，给权限即可
        ll {path}
        chown {user} -R {path}
        chmod 755 -R {path}
        ```
    - 问题：**把ssh-key加入git的deploy-keys列表**

        ```
        su {user} && cat ~/.ssh/id_rsa.pub
        打开 github/gitlab/bitbucket 网站, 添加 ssh-key 到ssh-keys列表
        ```

- 目标机器部署出错，请确认php进程{local_user}用户ssh-key加入目标机器的{remote_user}用户ssh-key信任列表，且{remote_user}有目标机器发布版本库{path}写入权限。详细错误：{error}
    - 问题：**请确认php进程{local_user}用户ssh-key加入目标机器的{remote_user}用户ssh-key信任列表**

        ```
        添加机器信任，还是没理解请百度吧（因为太多的同学问这问题，实在没办法只能这么啰嗦）
        su {local_user} && ssh-copy-id -i ~/.ssh/id_rsa.pub remote_user@remote_server
        # need remote_user's password
        ```
    - 问题：**{remote_user}有目标机器发布版本库{path}写入权限**

        ```
        su remote_user
        ll {path}
        chown {remote_user} -R {path}
        chmod 755 -R {path}
        ````