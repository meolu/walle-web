FROM python:2.7

MAINTAINER from Alenx<alenx.hai@live.com>

RUN mkdir /opt/walle-web && mkdir -p /data/walle

ADD ./requirements/prod.txt /usr/app/

RUN pip install -r /usr/app/prod.txt -i https://mirrors.aliyun.com/pypi/simple

VOLUME /root/.ssh/

EXPOSE 5000

CMD ["/bin/bash"]
