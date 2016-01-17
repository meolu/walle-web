The Most Detailed Installation Guide
====================================

A linux server which configures LAMP/LNMP and git/svn, this is all. Any question refer to [Q&A](https://github.com/meolu/walle-web/blob/master/docs/faq.md)

Requirements
------------

* Bash(git、ssh、rsync)
* LNMP、LAMP(php5.4+)
* Composer


1.Clone Repo
------------
```
mkdir -p /data/www/walle-web && cd /data/www/walle-web  # A place to store
git clone git@github.com:meolu/walle-web.git .          # clone is easy for update
```



2.Setup Mysql Connection
------------------------
```
vi config/web.php +12
'db' => [
    'class'     => 'yii\db\Connection',
    'dsn'       => 'mysql:host=127.0.0.1;dbname=walle', # Create a db first
    'username'  => 'username',                          # username
    'password'  => 'password',                          # password
    'charset'   => 'utf8',
],
```

3.Install Composer(skip if uou have composer)
---------------------------
```
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer  # Add to the PATH
```

4.Composer Install For Vendor
-----------------------------
```
cd walle-web
composer install --prefer-dist --no-dev --optimize-autoloader -vvvv
```

5.Init Walle
------------
```
cd walle-web
./yii walle/setup # yes
```

Upgrade Walle
-------------
```
cd walle-web
./yii walle/upgrade
```


6.Configure Nginx/Apache
-----------------
**50x maybe caused by the incomplete of step 1-5, check up yourself: )**

**if nginx/apache's configuration is not corect, you may see 40x page on step 7, check up yourself: )**

**nginx example**
```
server {
    listen       80;
    server_name  walle.huamanshu.com; # change to your host
    root /the/dir/of/walle-web/web;   # root is walle-web/web
    index index.php;

    # suggest access
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

**apache example**
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

7.Congratulations：）
--------
visit：localhost

of course, it would be nginx's server_name, such as walle.huamanshu.com, just visit walle.huamanshu.com.



