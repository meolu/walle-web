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
        wget https://bootstrap.pypa.io/3.3/get-pip.py
        python get-pip.py
    fi
    pip install virtualenv
    if [ ! -d "venv" ]; then
        virtualenv --no-site-packages venv # 注意:安装失败请指定python路径. mac 可能会有用anaconda的python. 请不要mac试用, 麻烦多多
    fi
    echo "安装/更新可能缺少的依赖: mysql-community-devel gcc gcc-c++ python-devel"
    sudo yum install -y mysql-devel gcc gcc-c++ python-devel MySQL-python
    requirement
    echo "************************************************"
    echo -e "\033[32m init walle success \033[0m"
    echo -e "\033[32m welcome to walle 2.0 \033[0m"
}

function requirement() {
    source ./venv/bin/activate
    pip install -r ./requirements/prod.txt
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
    cd $(dirname $0)
    echo -e "建议先暂存本地修改\033[33m git stash\033[0m，更新后再弹出\033[33m git stash pop\033[0m，处理冲突。"
    source venv/bin/activate
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
        requirement
        migration
        echo -e "\033[32m walle 更新成功. \033[0m \033[33m 建议重启服务 sh admin.sh restart\033[0m"
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
