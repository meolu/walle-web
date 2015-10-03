Walle - A Deployment Tool
=========================
Walle is a deployment tool written in PHP with yii2 out of the box.

See [walle](http://www.huamanshu.com/walle.html) for more information and documentation.

Requirements
------------

* bash(git、ssh)
* LNMP、LAMP(php5.4+)
* composer

That's all! It's base package of PHP envirament!


Installation
------------
```
git clone git@github.com:meolu/walle-web.git
cd walle-web
vi config/web.php # set up module db mysql connection info
composer install  # error cause by bower-asset, install：composer install global require "fxp/composer-asset-plugin:*"
./yii migrate/up  # migrate database
```


Quick Start
-------------

* set up nginx/apache webroot `walle-web/web`
* config email smtp（config your company's email smtp after trying in case leakaging Information）
    ```php
    vi config/params.php
    'support.email' => 'service@huamanshu.com', // the same with `username` of the module of `config/web.php`
    'mail-suffix'   => [
        'huamanshu.com',
    ]

    vi config/web.php +25
    # config module of mail smtp
    'class'      => 'Swift_SmtpTransport',
    'host'       => 'ip or host',            # smtp host
    'username'   => 'service@huamanshu.com', # smtp send user
    'password'   => 'password',              # smtp password
    'port'       => 25,                      # smtp port
    'encryption' => 'tls',                   # smtp agreement
    ```
* signup a admin user(`admin/admin` exists),then config a project
* signup a develop user(`demo/demo` exists),submit a task
* admin audit task
* deveop deploy the audited task



To Do List
----------

* the same project add lock while deploying
* a manager of static source

Update
-----------------
```
git pull
./yii migrate
```


screenshot
----------
#### signup a admin/developer
![](https://raw.github.com/meolu/walle-web/master/screenshots/login.png)

#### sumbit a task
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-submit.jpg)

#### list of task
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-dev-list.jpg)

#### project config
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle-config-edit.jpg)

#### demo show
![](https://raw.github.com/meolu/walle-web/master/screenshots/walle.gif)

## CHANGELOG
[CHANGELOG](https://github.com/meolu/walle-web/blob/master/CHANGELOG.md)


Discussing
----------
- [submit issue](https://github.com/meolu/walle-web/issues/new)
- QQ: 482939318
