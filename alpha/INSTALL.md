# Walle 2.0
Welcome to Walle 2.0. Alpha is a unstable version, please waiting for developer's released tag. If you Have the ability to have try, just try, but I hove no time for truble shooting, fix it by yourself, or submit a issue.

# Developer
## 给此项目标个星 | Star this repo
请为我们做开源项目的付出予以肯定和支持，感谢你的信任。

## Clone code
```
git clone git@github.com:meolu/walle-web.git
```

## Nginx config
Don't forget to restart nginx
```
upstream webservers {
    server dev.admin.walle-web.io:5000 weight=1;
}

server {
    listen       80;
    server_name  api.walle-web.io dev.admin.walle-web.io;
    access_log   /usr/local/nginx/logs/walle.log main;
    index index.html index.htm;

    location / {
        try_files $uri $uri/ /index.html;
        add_header access-control-allow-origin *;
        root /Users/wushuiyong/workspace/meolu/walle-fe/dist;
    }

    location ^~ /api/ {
        add_header access-control-allow-origin *;
        proxy_pass      http://webservers;
        proxy_set_header X-Forwarded-Host $host:$server_port;
        proxy_set_header  X-Real-IP  $remote_addr;
        proxy_set_header    Origin        $host:$server_port;
        proxy_set_header    Referer       $host:$server_port;
    }

    location ^~ /socket.io/ {
        add_header access-control-allow-origin *;
        proxy_pass      http://webservers;
        proxy_set_header X-Forwarded-Host $host:$server_port;
        proxy_set_header  X-Real-IP  $remote_addr;
        proxy_set_header    Origin        $host:$server_port;
        proxy_set_header    Referer       $host:$server_port;
        proxy_set_header Host $http_host;
        proxy_set_header X-NginX-Proxy true;

        # WebScoket Support
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

## Hosts
```
vi /etc/hosts

# add one line
127.0.0.1  dev.admin.walle-web.io
```

## Data Migration
```
mysql -hxx -uxx -p -e'source walle_python_with_data.sql;'
```

## Config environment
安装pip。{PROJECT} 默认指项目。 | install pip. {PROJECT} means walle
```
pip install virtualenv

cd {PROJECT}
virtualenv venv
source venv/bin/activate
pip install -r requirements/dev.txt
```

## Config code
You will know to change what to suit for you environment
```
vi walle/config/settings_dev.py
```

## Start
```
sh run.sh  # start with debug mode
```