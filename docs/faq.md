常见问题以及解决办法
=================

1.composer安装速度慢
----------------
好吧，我已经猜到会有人问有没有现成的，有！ 

下载[百度网盘](http://pan.baidu.com/s/1c0wiuyc)，解压vendor放到walle-web根目录即可。


2.第一次使用composer可能会出现的问题：1 没有添加git的token
-------------------------------------------------

>Could not fetch https://api.github.com/repos/jquery/jquery, please create a GitHub OAuth token to go over the API rate limit
Head to https://github.com/settings/tokens/new?scopes=repo&description=Composer+on+localhost+2015-10-08+1123
to retrieve a token. It will be stored in "/root/.composer/auth.json" for future use by Composer.
Token (hidden): 

**解决办法：**

* 复制提示里的地址到浏览器，点击生成git token，如上面的：https://github.com/settings/tokens/new?scopes=repo&description=Composer+on+localhost+2015-10-08+1123
* 复制token到命令行，认证，继续

3.第一次使用composer可能会出现的问题：2 composer install 可能会出现的错误
-----------------------------------------------------------------

>Loading composer repositories with package information
Installing dependencies (including require-dev)
Your requirements could not be resolved to an installable set of packages.
>
>  Problem 1
>    - yiisoft/yii2 2.0.x-dev requires bower-asset/jquery 2.1.*@stable | 1.11.*@stable -> no matching package found.
> ....

**解决办法**：`composer global require "fxp/composer-asset-plugin:*"`

4.如何添加用户key到git的ssh-keys列表
-------------------------------
```
su - www                 # 假如www为你的php进程用户
ssh-keygen -t rsa        # 如果你都没有生成过rsa_key的话
cat ~/.ssh/id_rsa.pub    # 复制
打开github/gitlab添加到你的ssh-keys或者deploy-keys里
```


5.如何添加用户ssh-key到目标机群部署用户ssh-key信任
-------------------------------------------
**宿主机操作**
```
ps aux|grep php          # 假如www_php为你的php进程用户
su - www_php             # 切换用户
ssh-keygen -t rsa        # 如果你都没有生成过rsa_key的话，如果有则跳过
ssh-copy-id -i ~/.ssh/id_rsa.pub www_remote@remote_host  # 加入目标机群信任，需要输入www_remote密码
```

6.数据导入失败
----------
缺少pdo扩展，解决办法：添加pdo扩展
```
ubuntu
apt-get install php5 php5-fpm php5-mysql

或者在源码包里编译
cd php-src/ext/pdo_mysql
phpize
./configure --with-php-config=/php/install/dir/bin/php-config
make && make install
vi php.ini # 添加pdo_mysql.so
restart php-fpm
```

7.nginx简单配置
----------------
```
server {
    listen       80;
    server_name  walle.compony.com; # 改你的host
    root /the/dir/of/walle-web/web; # 根目录为web
    index index.php;

    # 建议放内网
    allow 192.168.0.0/24;
    deny all;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}
```


8.apache简单配置
----------------

```
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule php5_module        /usr/lib64/httpd/modules/libphp5.so
<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>
<VirtualHost *:80>
ServerName walle.*.com
DocumentRoot /code/walle-web/web
ErrorLog logs/dev.-error.log
CustomLog logs/dev.-accesslog common
    <Directory "/code/walle-web/web">
      Options  FollowSymLinks
        AllowOverride ALL
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```

9.切换用户（www）时：this account is currently not available
----------------------------------------------------------

```
cat /etc/passwd | grep www # 查看是否为 /sbin/nolgin
```

解决办法：
```
vipw /etc/passwd
修改/sbin/nolgin为/bin/bash
```