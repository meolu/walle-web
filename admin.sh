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
    export FLASK_DEBUG=1
    nohup python $APP &
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
    * )
        echo "****************"
        echo "no command"
        echo "****************"
        ;;
esac
