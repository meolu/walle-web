Walle's configuration guide
==========================

1.Requirements：
--------------

* The ssh-key of the user of php process in host should be added to github/gitlab/bitbucket's ssh-keys. Of course you can specify the repo url input like this: `https://username:password@github.com/meolu/walle-web.git`, but it is not recommended.
    * how to find out the user of php process:
        ```shell
        ps aux|grep php
        ```
    * how to find out the ssh-key of user:
        ```shell
        su user-name && cat ~/.ssh/id_rsa.pub
        ```
* The ssh-key of the user of php process in host should be added to target servers authorized_keys
    * how to add a ssh-key to remote server:
        ```shell
        su user-name && ssh-copy-id -i ~/.ssh/id_rsa.pub remote_user@remote_server
        # need remote_user's password
        ```

2.Configuration
---------------

![](https://github.com/meolu/walle-web/blob/master/screenshots/base-git.jpg)
![](https://github.com/meolu/walle-web/blob/master/screenshots/task.jpg)

3.Detection
-----------

if project's configuration works after detection, have a try to deploy : )

![](https://github.com/meolu/walle-web/blob/master/screenshots/detection.png)
