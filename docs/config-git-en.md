Walle's configuration guide
==========================

1.Requirements：
--------------

* The ssh-key of the user of php process in host should be added to github/gitlab/bitbucket's ssh-keys. Of course you can specify the repo url input like this: `https://username:password@github.com/meolu/walle-web.git`, but it is not recommended.
    * how to find out the user of php process:

        ```
        ps aux|grep php
        ```
    * how to find out the ssh-key of user:

        ```
        su user-name && cat ~/.ssh/id_rsa.pub
        ```
* The ssh-key of the user of php process in host should be added to target servers authorized_keys
    * how to add a ssh-key to remote server:

        ```
        su user-name && ssh-copy-id -i ~/.ssh/id_rsa.pub remote_user@remote_server
        # need remote_user's password
        ```

2.Configuration
---------------

![](https://github.com/meolu/walle-web/blob/master/screenshots/task-en.jpg)

3.Detection
-----------

if project's configuration works after detection, have a try to deploy : )

![](https://github.com/meolu/walle-web/blob/master/screenshots/detection-en.png)

4.Detection errors and solutions
-------------------------------

- hosted server error: please make sure the user {user} of php process have the access permission of {path}，and add the ssh-key to github/gitlab\'s deploy-keys。error：{error}
    - make sure the user {user} have access permission of {path}

        ```
        ll {path}
        chown {user} -R {path}
        chmod 755 -R {path}
        ```
    - add the ssh-key to github/gitlab\'s deploy-keys

        ```
        su {user} && cat ~/.ssh/id_rsa.pub
        open github/gitlab/bitbucket, add the ssh-key
        ```
- target server error: please make sure the ssh-key of user {local_user} of php process is added to target servers\'s user {remote_user}\'s authorized_keys, and {remote_user} have the access permission of {path} on target servers. error：{error}
    - please make sure the ssh-key of user {local_user} of php process is added to target servers\'s user {remote_user}\'s authorized_keys

        ```
        su {local_user} && ssh-copy-id -i ~/.ssh/id_rsa.pub remote_user@remote_server
        # need remote_user's password
        ```
    - {remote_user} have the access permission of {path} on target servers

        ```
        su remote_user
        ll {path}
        chown {remote_user} -R {path}
        chmod 755 -R {path}
        ````

