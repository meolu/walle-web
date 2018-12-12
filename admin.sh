#########################################################################
# File APP: admin.sh
# Author: wushuiyong
# mail: wushuiyong@walle-web.io
# Created Time: 2018年11月03日 星期六 06时09分46秒
#########################################################################
#!/bin/bash

APP="waller.py"

function init() {
    echo "init walle"
    echo "----------------"
    which pip
    if [ $? != "0" ]; then
        exit 1
    fi
    pip install virtualenv
    rm -rf venv
    virtualenv --no-site-packages venv # 注意:安装失败请指定python路径. mac 可能会有用anaconda的python
    source ./venv/bin/activate
    pip install -r ./requirements/prod.txt
    echo "************************************************"
    echo -e "\033[32m init walle success \033[0m"
    echo -e "\033[32m welcome to walle 2.0 \033[0m"
}

function start() {
    echo "start walle"
    echo "----------------"
    source ./venv/bin/activate
    mkdir -p logs
    nohup python $APP >> logs/runtime.log 2>&1 &
    echo -e "Starting walle:                 [\033[32m ok \033[0m]"
    echo -e "runtime: \033[32m logs/runtime.log \033[0m"
    echo -e "error: \033[32m logs/error.log \033[0m"
}
 
function stop() {
    echo "stop walle"
    echo "----------------"
    # 获取进程 PID
    PID=$(ps -ef | grep $APP | grep -v grep | awk '{print $2}') 
    # 杀死进程
    kill -9 $PID
}
 
function restart() {
    echo "restart walle"
    echo "----------------"
    stop
    start
}

function upgrade() {
    echo "upgrade walle"
    echo "----------------"
    cd `dirname $0`
    git pull
}

function migration() {
    echo "migration walle"
    echo "----------------"
    source venv/bin/activate
    export FLASK_APP=waller.py
    flask db upgrade
    if [ $? == "0" ]; then
        echo -e "Migration:                 [\033[32m ok \033[0m]"
    else
        echo -e "Migration:                 [\033[31m fail \033[0m]"
    fi
}

case "$1" in
    init )
        echo "************************************************"
        init
        echo "************************************************"
        ;;
    start )
        echo "************************************************"
        start
        echo "************************************************"
        ;;
    stop )
        echo "************************************************"
        stop
        echo "************************************************"
        ;;
    restart )
        echo "************************************************"
        restart
        echo "************************************************"
        ;;
    upgrade )
        echo "************************************************"
        upgrade
        migration
        echo "************************************************"
        ;;
    migration )
        echo "************************************************"
        migration
        echo "************************************************"
        ;;
    * )
        echo "************************************************"
        echo "Usage: sh admin {init|start|stop|restart|upgrade|migration}"
        echo "************************************************"
        ;;
esac
