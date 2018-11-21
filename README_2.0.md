# walle-web

walle-web.io a deployment kit


[![Build Status](https://travis-ci.org/meolu/walle-web.svg?branch=development)](https://travis-ci.org/meolu/walle-web)

Quickstart
----------

```
# 开发分支尝鲜
git clone https://github.com/meolu/walle-web
cd walle-web
git checkout development # 开发分支

# 配置环境
pip install virtualenv
virtualenv venv
source venv/bin/activate
pip install -r requirements/dev.txt


# 数据导入 mysql 新建一个 walle_python 库
> create database walle_python;
> source walle_python_with_data.sql

后期精细化migration
flask db init
flask db migrate
flask db upgrade

# 修改数据库连接（自己找下,小小地考验下）
vi walle/config/settings.py


# 运行（内含Flask的一些配置）
sh run.sh

# 怎么可能没有标准的单元测试呢
sh test.sh

```
勾搭下
---------
人脉也是一项非常重要能力，请备注姓名@公司，谢谢：）

<img src="https://raw.githubusercontent.com/meolu/walle-web/master/docs/weixin.wushuiyong.jpg" width="244" height="314" alt="吴水永微信" align=left />

<img src="https://raw.githubusercontent.com/meolu/walle-web/master/docs/chenfengjuan.jpeg" width="244" height="314" alt="孙恒哲微信" align=left />

<img src="https://raw.githubusercontent.com/meolu/walle-web/master/docs/yexinhao.jpeg" width="280" height="314" alt="叶歆昊微信" align=left />

