FROM python:2.7

RUN mkdir /opt/walle

COPY ./requirements/prod.txt /usr/app/

RUN pip install futures && pip install -r /usr/app/prod.txt -i https://mirrors.aliyun.com/pypi/simple

EXPOSE 5000

CMD python /opt/walle/waller.py
