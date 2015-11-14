FAQ
====

1.Token is need the first time to use github
-------------------------------------------------

>Could not fetch https://api.github.com/repos/jquery/jquery, please create a GitHub OAuth token to go over the API rate limit
Head to https://github.com/settings/tokens/new?scopes=repo&description=Composer+on+localhost+2015-10-08+1123
to retrieve a token. It will be stored in "/root/.composer/auth.json" for future use by Composer.
Token (hidden): 

**Solution：**

* copy the url in the screen to browser，general a git token, such as above：https://github.com/settings/tokens/new?scopes=repo&description=Composer+on+localhost+2015-10-08+1123
* pasty the token to command line, go next.

2.bower-asset/jquery may be need the first time to use composer when composer install
-----------------------------------------------------------------

>Loading composer repositories with package information
Installing dependencies (including require-dev)
Your requirements could not be resolved to an installable set of packages.
>
>  Problem 1
>    - yiisoft/yii2 2.0.x-dev requires bower-asset/jquery 2.1.*@stable | 1.11.*@stable -> no matching package found.
> ....

**Solution**：`composer global require "fxp/composer-asset-plugin:*"`

3.How to add user's ssh-key to github/gitlab's ssh-keys list
------------------------------------------------------------
```
su - www                 # suppose www is the user of php process
ssh-keygen -t rsa        # skip if you have generaled the rsa key
cat ~/.ssh/id_rsa.pub    # copy
open github/gitlab's website, add your key to ssh-keys or deploy-keys
```


4.How to add user's ssh-key to server
-------------------------------------------
**on host**
```
ps aux|grep php          # suppose www is the user of php process
su - www                 # switch to www
ssh-keygen -t rsa        # skip if you have generaled the rsa key
ssh-copy-id -i ~/.ssh/id_rsa.pub remote_user@remote_host  # add key to remote authorized_keys, need remote_user's password
```

5.Import data failed
----------
Dependent on pdo extension. Solution：add pdo extension
```
ubuntu
> apt-get install php5 php5-fpm php5-mysql

compile in php source
> cd php-src/ext/pdo_mysql
> phpize
> ./configure --with-php-config=/php/install/dir/bin/php-config
> make && make install
> vi php.ini # add new line: extension=pdo_mysql.so
restart php-fpm
```

6.Nginx configuration example
-----------------------------
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

7.apache configuration example
------------------------------

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