#########################################################################
# File APP: admin.sh
# Author: wushuiyong
# mail: wushuiyong@walle-web.io
# Created Time: 2018年11月03日 星期六 06时09分46秒
#########################################################################
#!/bin/bash

APP="waller.py"
 
function start() {
    echo "start walle"
    echo "----------------"
    source ./venv/bin/activate
    mkdir -p logs
    nohup python $APP >> logs/runtime.log 2>&1 &
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
    export FLASK_APP=waller.py
    flask db upgrade
}

case "$1" in
    start )
        echo "****************"
        start
        echo "****************"
        ;;
    stop )
        echo "****************"
        stop
        echo "****************"
        ;;
    restart )
        echo "****************"
        restart
        echo "****************"
        ;;
    upgrade )
        echo "****************"
        upgrade
        migration
        echo "****************"
        ;;
    migration )
        echo "****************"
        migration
        echo "****************"
        ;;
    * )
        echo "****************"
        echo "no command"
        echo "****************"
        ;;
esac
