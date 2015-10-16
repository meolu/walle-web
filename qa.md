常见问题以及解决办法
=================
composer安装速度慢
----------------
好吧，我已经猜到会有人问有没有现成的，有！ 

下载[百度网盘](http://pan.baidu.com/s/1c0wiuyc)，解压vendor放到walle-web根目录即可。


第一次使用composer可能会出现的问题：1 没有添加git的token
-------------------------------------------------

>Could not fetch https://api.github.com/repos/jquery/jquery, please create a GitHub OAuth token to go over the API rate limit
Head to https://github.com/settings/tokens/new?scopes=repo&description=Composer+on+localhost+2015-10-08+1123
to retrieve a token. It will be stored in "/root/.composer/auth.json" for future use by Composer.
Token (hidden): 

**解决办法：**

* 复制提示里的地址到浏览器，点击生成git token，如上面的：https://github.com/settings/tokens/new?scopes=repo&description=Composer+on+localhost+2015-10-08+1123
* 复制token到命令行，认证，继续

第一次使用composer可能会出现的问题：2 composer install 可能会出现的错误
-----------------------------------------------------------------

>Loading composer repositories with package information
Installing dependencies (including require-dev)
Your requirements could not be resolved to an installable set of packages.
>
>  Problem 1
>    - yiisoft/yii2 2.0.x-dev requires bower-asset/jquery 2.1.*@stable | 1.11.*@stable -> no matching package found.
> ....

**解决办法**：`composer global require "fxp/composer-asset-plugin:*"`


nginx简单配置
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