#!/usr/bin/env bash
# name : Alenx

#docker build -t alenx/walle-web:2.0 . -f Dockerfile.web
#docker build -t alenx/walle-python:2.1 . -f Dockerfile.app
#docker build -t alenx/walle-java:2.1 . -f Dockerfile.java


#docker push alenx/walle-web:2.0
docker push alenx/walle-python:2.1
#docker push alenx/walle-java:2.1


#docker rmi alenx/walle-web:2.0
#docker rmi alenx/walle-python:2.0