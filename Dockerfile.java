FROM centos

MAINTAINER from Alenx<liqinghai058@live.com>

ENV PATH /usr/local/bin:$PATH

ENV PYTHONIOENCODING UTF-8

ADD ./requirements/prod.txt /usr/app/

RUN yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm \
    && yum -y install gcc gcc-c++ MySQL-python mysql-devel python-pip python-devel git \
    && yum -y install openssl-devel zlib-devel bzip2-devel \
    && yum -y install libxml2-devel libxslt-devel ncurses-devel which\
    && yum clean all && rm -rf /tmp/* rm -rf /var/cache/yum/* \
    && pip install -r /usr/app/prod.txt -i https://mirrors.aliyun.com/pypi/simple

COPY jdk1.8.tar.gz /usr/local/

COPY maven.tar.gz /usr/local/

RUN cd /usr/local/ && tar -zxf jdk1.8.tar.gz && tar -zxf maven.tar.gz \
    && rm -rf *.tar.gz

ENV JAVA_HOME=/usr/local/jdk1.8
ENV M2_HOME=/usr/local/maven
ENV CLASSPATH=.:$JAVA_HOME/lib/dt.jar:$JAVA_HOME/lib/tools.jar
ENV PATH=${JAVA_HOME}/bin:${M2_HOME}/bin:$PATH
RUN ln -s /usr/local/jdk1.8/bin/java /usr/bin/java \
    && ln -s /usr/local/jdk1.8/bin/java /usr/sbin/java \
    && ln -s /usr/local/maven/bin/mvn /usr/bin/mvn

ADD . /opt/walle-web/

RUN cd /opt/walle-web/ && rm -rf *.gz fe/ gateway/ tests/ requirements/

CMD ["/bin/bash"]