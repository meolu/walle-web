FROM python:2.7

MAINTAINER from Alenx<liqinghai058@live.com>

ADD ./requirements/prod.txt /usr/app/

RUN pip install -r /usr/app/prod.txt -i https://mirrors.aliyun.com/pypi/simple

ADD . /opt/walle-web/

RUN cd /opt/walle-web/ && rm -rf *.gz fe/ gateway/ tests/ requirements/

CMD ["/bin/bash"]