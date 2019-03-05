#########################################################################
# File APP: admin.sh
# Author: wushuiyong
# mail: wushuiyong@walle-web.io
# Created Time: 2018年11月03日 星期六 06时09分46秒
#########################################################################
# Update Time : 2019-03-05
# Author: alenx <alenx.hai@gmail.com>
# -->>  新增ubuntu初始化，全面支持Ubuntu环境(16.x/18.x)
#########################################################################
#!/bin/bash

APP="waller.py"

function init() {
    echo "Initing walle"
    echo "----------------"
    SystemName

    pip install virtualenv
    if [ ! -d "venv" ]; then
        virtualenv --no-site-packages venv # 注意:安装失败请指定python路径. mac 可能会有用anaconda的python. 请不要mac试用, 麻烦多多
    fi

    requirement
    echo "************************************************"
    echo -e "\033[32m init walle success \033[0m"
    echo -e "\033[32m welcome to walle 2.0 \033[0m"
}

function requirement() {
    source ./venv/bin/activate
    pip install -r ./requirements/prod.txt
}

function SystemName() {
    source /etc/os-release
    case $ID in
        centos|fedora|rhel)
            which pip
            if [ $? != "0" ]; then
                wget https://bootstrap.pypa.io/3.3/get-pip.py
                python get-pip.py
            fi
            echo "安装/更新可能缺少的依赖: mysql-community-devel gcc gcc-c++ python-devel"
            sudo yum install -y mysql-devel gcc gcc-c++ python-devel MySQL-python
            ;;

        debian|ubuntu|devuan)
            echo "安装/更新可能缺少的依赖: ibmysqld-dev gcc gcc-c++ python-dev"
            sudo apt update -y
            sudo apt install -y libmysqld-dev python-dev virtualenv python-pip
            ;;

        *)
            exit 1
            ;;
    esac
}

function start() {
    echo "Starting walle"
    echo "----------------"
    source ./venv/bin/activate
    mkdir -p logs
    nohup python ${APP} >> logs/runtime.log 2>&1 &
    echo -e "Start walle:                 [\033[32m ok \033[0m]"
    echo -e "runtime: \033[32m logs/runtime.log \033[0m"
    echo -e "error: \033[32m logs/error.log \033[0m"
}

function stop() {
    echo "Stoping walle"
    echo "----------------"
    # 获取进程 PID
    PID=$(ps -ef | grep ${APP} | grep -v grep | awk '{print $2}')
    # 杀死进程
    kill -9 ${PID}
    echo -e "Stop walle:                  [\033[32m ok \033[0m]"
}

function restart() {
    stop
    echo ""
    start
}

function upgrade() {
    echo "Upgrading walle"
    echo "----------------"
    cd $(dirname $0)
    echo -e "建议先暂存本地修改\033[33m git stash\033[0m，更新后再弹出\033[33m git stash pop\033[0m，处理冲突。"
    source ./venv/bin/activate
    git pull
}

function walle_banner() {

echo "                                                                                            ";
echo "                                                          llllllllllllll                     ";
echo "                                                           l::::l l::::l                     ";
echo "wwwwwww           wwwww           wwwwww aaaaaaaaaaaaa     l::::l l::::l     eeeeeeeeeeee    ";
echo " w:::::w         w:::::w         w:::::w a::::::::::::a    l::::l l::::l   ee::::::::::::ee  ";
echo "  w:::::w       w:::::::w       w:::::w  aaaaaaaaa:::::a   l::::l l::::l  e::::::eeeee:::::ee";
echo "   w:::::w     w:::::::::w     w:::::w            a::::a   l::::l l::::l e::::::e     e:::::e";
echo "    w:::::w   w:::::w:::::w   w:::::w      aaaaaaa:::::a   l::::l l::::l e:::::::eeeee::::::e";
echo "     w:::::w w:::::w w:::::w w:::::w     aa::::::::::::a   l::::l l::::l e:::::::::::::::::e ";
echo "      w:::::w:::::w   w:::::w:::::w     a::::aaaa::::::a   l::::l l::::l e::::::eeeeeeeeeee  ";
echo "       w:::::::::w     w:::::::::w     a::::a    a:::::a   l::::l l::::l e:::::::e           ";
echo "        w:::::::w       w:::::::w      a::::a    a:::::a   l::::l l::::l e::::::::e          ";
echo "         w:::::w         w:::::w       a:::::aaaa::::::a   l::::l l::::l  e::::::::eeeeeeee  ";
echo "          w:::w           w:::w         a::::::::::aa::a   l::::: l:::::l  ee:::::::::::::e  ";
echo "           www             www           aaaaaaaaaa  aaaa llllllllllllllll    eeeeeeeeeeeeee  ";
echo "                                                                                            ";


}

function migration() {
    echo "Migration walle"
    echo "----------------"
   source ./venv/bin/activate
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
        walle_banner
        init
        ;;
    start )
        walle_banner
        start
        ;;
    stop )
        walle_banner
        stop
        ;;
    restart )
        walle_banner
        restart
        ;;
    upgrade )
        walle_banner
        upgrade
        requirement
        migration
        echo -e "\033[32m walle 更新成功. \033[0m \033[33m 建议重启服务 sh admin.sh restart\033[0m"
        ;;
    migration )
        walle_banner
        migration
        ;;
    * )
        walle_banner
        echo "************************************************"
        echo "Usage: sh admin {init|start|stop|restart|upgrade|migration}"
        echo "************************************************"
        ;;
esac