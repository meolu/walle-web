FROM nginx:latest

MAINTAINER from Alenx<liqinghai058@live.com>

COPY ./fe/ /data/walle/

COPY ./gateway/nginx/default.conf /etc/nginx/conf.d/default.conf

CMD ["nginx", "-g", "daemon off;"]