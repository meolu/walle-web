![](https://raw.github.com/meolu/walle-web/master/docs/logo.jpg)

Walle - A Deployment Tool
=========================
[![Build Status](https://travis-ci.org/meolu/walle-web.svg?branch=master)](https://travis-ci.org/meolu/walle-web)
[![Packagist](https://img.shields.io/packagist/v/meolu/walle-web.svg)](https://packagist.org/packages/meolu/walle-web)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

A web deployment tool, Easy for configuration, Fully functional, Smooth interface, Out of the box.
support git/svn Version control system, no matter what language you are, php/java/ruby/python, just as jenkins. you can deploy the code or output to multiple servers easily by walle.

[Home Page](https://www.walle-web.io) | [官方主页](https://www.walle-web.io) | [中文说明](https://github.com/meolu/walle-web/blob/master/docs/README-zh.md) | [文档手册](https://www.walle-web.io/docs/).

Now, there are more than hundreds of companies hosted walle for deployment, star walle if you like : )

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
* Multi-process multi-server file transfer (Ansible).


Requirements
------------

* Bash(git、ssh)
* LNMP/LAMP(php5.4+)
* Composer
* Ansible(Optional)

That's all. It's base package of PHP environment!


Installation
------------
```
git clone git@github.com:meolu/walle-web.git
cd walle-web
vi config/local.php # set up module db mysql connection info
composer install  # error cause by bower-asset, install：composer global require "fxp/composer-asset-plugin:*"
./yii walle/setup # init walle
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
        'huamanshu.com',  // e.g: allow xyz@huamanshu.com only
    ]
    ```

* Configure email smtp
    ```php
    vi config/local.php

    'transport' => [
            'host'       => 'smtp.huamanshu.com',
            'username'   => 'service@huamanshu.com',
            'password'   => 'K84erUuxg1bHqrfD',
            'port'       => 25,
            'encryption' => 'tls',
        ],
        'messageConfig' => [
            'charset' => 'UTF-8',
            'from'    => ['service@huamanshu.com' => '花满树出品'], 
        ],
    ```

* Configure the path for log
    ```php
    vi config/params.php

    'log.dir'   => '/tmp/walle/',
    ```

* Configure language
    ```php
    vi config/local.php

    'language'   => 'en',  // zh-CN => 中文,  en => English
    ```


To Do List
----------

- Travis CI integration
- Mail events：specify kinds of events
- Gray released：specify servers
- Websocket instead of poll
- A manager of static source
- Configure variables
- Support Docker
- Open api
- Command line

Update
-----------------
```
./yii walle/upgrade    # upgrade walle
```


Architecture
------------
#### git/svn, user, host, servers
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/en/static/walle-flow-relation-en.png)

#### deployment flow
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/en/static/walle-flow-en.png)

Screenshots
-----------

#### project config
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/en/static/walle-config-edit-en.jpg)

#### sumbit a task
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/en/static/walle-submit-en.jpg)

#### list of task
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/en/static/walle-dev-list-en.jpg)

#### demo show
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/en/static/walle-en.gif)

## CHANGELOG
[CHANGELOG](https://github.com/meolu/walle-web/releases)


Discussing
----------
- [submit issue](https://github.com/meolu/walle-web/issues/new)
- email: wushuiyong@huamanshu.com

勾搭下
--------
<img src="https://raw.githubusercontent.com/meolu/walle-web/feature-weixin/docs/weixin.wushuiyong.jpg" width="244" height="314" alt="吴水永微信" align=left />
