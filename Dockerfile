FROM python:2.7

ENV FLASK_APP waller.py

WORKDIR /usr/app/

COPY ./requirements/prod.txt .
RUN pip install futures
RUN mkdir logs

RUN pip install -r prod.txt -i https://mirrors.aliyun.com/pypi/simple

COPY . .
RUN flask db upgrade

CMD python waller.py
