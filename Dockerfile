FROM python:2.7.1

WORKDIR /usr/app
COPY ./requirements/prod.txt ./requirements.txt
RUN pip install -r requirements.txt -i https://mirrors.aliyun.com/pypi/simple

COPY . .
RUN sh admin.sh migration

CMD python waller.py