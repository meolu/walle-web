最最最详细安装指南
===============

以下安装，均在**宿主机**（一台配置了LAMP/LNMP的linux机器，并且安装git/svn）上操作，如有问题，详见[Q&A](https://github.com/meolu/walle-web/blob/master/docs/faq.md)

依赖
---

* bash(git、ssh)
* LNMP、LAMP(php5.4+)
* composer


1.代码检出
----------
```
mkdir -p /data/www/walle-web && cd /data/www/walle-web  # 新建目录
git clone git@github.com:meolu/walle-web.git .       # 代码检出
```



2.设置mysql连接
--------------
```
vi config/web.php +12
'db' => [
    'class'     => 'yii\db\Connection',
    'dsn'       => 'mysql:host=127.0.0.1;dbname=walle', # 新建数据库walle
    'username'  => 'username',                          # 连接的用户名
    'password'  => 'password',                          # 连接的密码
    'charset'   => 'utf8',
],
```

3.安装composer，如果已安装跳过
---------------------------
```
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer  # PATH目录
```

4.安装vendor
-----------
```
cd walle-web
composer install --prefer-dist --no-dev --optimize-autoloader -vvvv
```

5.初始化项目
----------
```
cd walle-web
./yii run/setup # 需要你的yes
```


6.配置nginx/apache
-----------------
**凡是在第7步刷新页面看到50x均是前5步安装不完整，自行检查**

**凡是在第7步刷新页面看到404均是nginx/apache配置不当，自行检查**

**nginx**简单配置
```
server {
    listen       80;
    server_name  walle.compony.com; # 改你的host
    root /the/dir/of/walle-web/web; # 根目录为web
    index index.php;

    # 建议放内网
    # allow 192.168.0.0/24;
    # deny all;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        try_files $uri = 404;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}
```

**apache**简单配置
-----------------

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

7.恭喜：）
--------
访问地址：localhost

当然，可能你配置nginx时的server_name是walle.compony.com时，配置本地hosts之后，直接访问：walle.compony.com亦可。



