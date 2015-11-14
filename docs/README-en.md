![](https://raw.github.com/meolu/walle-web/master/screenshots/logo.jpg)

Walle - A Deployment Tool
=========================
[![Build Status](https://travis-ci.org/meolu/walle-web.svg?branch=master)](https://travis-ci.org/meolu/walle-web)
[![Packagist](https://img.shields.io/packagist/v/meolu/walle-web.svg)](https://packagist.org/packages/meolu/walle-web)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

A web deployment tool, Easy for configuration, Fully functional, Smooth interface, Out of the box.
support git/svn Version control system, no matter what language you are, php/java/ruby/python, just as jenkins. you can deploy the code or output to multiple servers easily on walle, just
See [home page](http://www.huamanshu.com/walle-en.html) for more information, [中文说明](https://github.com/meolu/walle-web/blob/master/README-zh.md).

Now, there are more then ten companies hosted walle for deployment, star walle if you like : )

* Support git/svn Version control system.
* User signup by admin/develop identity.
* Developer submit a task, deploy task.
* Admin audit task.
* Multiple project.
* Multiple Task Parallel.
* Quick rollback.
* Group relation of project.
* Task of pre-deploy（e.g: test ENV var）.
* Task of post-deploy（e.g: mvn/ant, composer install for vendor）.
* Task of pre-release（e.g: stop service）.
* Task of post-release（e.g: restart service）.
* Check up file md5.


Requirements
------------

* Bash(git、ssh)
* LNMP/LAMP(php5.4+)
* Composer

That's all. It's base package of PHP environment!


Installation
------------
```
git clone git@github.com:meolu/walle-web.git
cd walle-web
vi config/web.php # set up module db mysql connection info
composer install  # error cause by bower-asset, install：composer global require "fxp/composer-asset-plugin:*"
./yii run/setup   # init walle
```
Or [The Most Detailed Installation Guide](https://github.com/meolu/walle-web/blob/master/docs/install-en.md), any questions refer to [FAQ](https://github.com/meolu/walle-web/blob/master/docs/faq-en.md)

Quick Start
-------------

* Signup a admin user(`admin/admin` exists), then configure a project, add member to the project, detect it.
    * [git demo](https://github.com/meolu/walle-web/blob/master/docs/config-git-en.md)
    * [svn demo](https://github.com/meolu/walle-web/blob/master/docs/config-svn-en.md)
* Signup a develop user(`demo/demo` exists), submit a deployment.
* Project admin audit the deployment.
* Developer deploy the deployment.


Custom
--------
you would like to adjust some params to make walle suited for your company.

* Set suffix of email while signing in
    ```php
    vi config/params.php

    'mail-suffix'   => [  // specify the suffix of email, multiple suffixes are allow.
        'huamanshu.com',  // e.g: allow xxx@huamanshu.com only
    ]
    ```

* Configure email smtp
    ```php
    vi config/web.php +25

    # 配置mail smtp模块
    'class'      => 'Swift_SmtpTransport',
    'host'       => 'smtp.huamanshu.com',    # smtp host
    'username'   => 'service@huamanshu.com', # smtp username
    'password'   => 'password',              # smtp password
    'port'       => 25,                      # smtp port
    'encryption' => 'tls',                   # smtp protocol


    vi config/params.php

    'support.email' => 'service@huamanshu.com', // 与config/web.php 中mail模块的username一致
    ```

* Configure the path for log
    ```php
    vi config/params.php

    'log.dir'   => '/tmp/walle/',
    ```

* Configure language
    ```php
    vi config/web.php +73

    'language'   => 'en',  # zh => 中文,  en => english
    ```


To Do List
----------

- Travis CI integration
- Mail events：specify kinds of events
- Gray released：specify servers
- Websocket instead of poll
- A manager of static source
- Configure variables;
- Support Docker
- Open api
- Command line

Update
-----------------
```
git pull
./yii migrate # update db
```


Architecture
------------
#### git/svn, user, host, servers
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-flow-relation-en.png)

#### deployment flow
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-flow-en.png)

Screenshots
-----------

#### project config
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-config-edit-en.jpg)

#### sumbit a task
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-submit-en.jpg)

#### list of task
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-dev-list-en.jpg)

#### demo show
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-en.gif)

## CHANGELOG
[CHANGELOG](https://github.com/meolu/walle-web/blob/master/docs/CHANGELOG.md)


Discussing
----------
- [submit issue](https://github.com/meolu/walle-web/issues/new)
- email: wushuiyong@huamanshu.com
- QQ Group: 482939318