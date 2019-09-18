# -*- coding: utf-8 -*-
"""
    walle-web
    Application configuration.
    注意: 带了 @TODO 的地方可能需要你的调整

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-24 07:05:35
    :author: wushuiyong@walle-web.io
"""

import os
from walle.config.settings import Config

SETTINGS = {
    "MYSQL_USER": "root",
    "MYSQL_PASSWORD": "walle",
    "MYSQL_DATABASE": "walle",
    "MYSQL_ROOT_PASSWORD": "walle",
    "MYSQL_HOST": "127.0.0.1",
    "MYSQL_PORT": 3306,
}


class ProdConfig(Config):
    """Production configuration."""
    ENV = 'prod'
    DEBUG = False
    SQLALCHEMY_ECHO = False

    # 服务启动 @TODO
    # HOST 修改为与 nginx server_name 一致.
    # 后续在web hooks与通知中用到此域名.
    HOST = '0.0.0.0'
    PORT = 5000
    # https True, http False
    SSL = False

    # 数据库设置 @TODO
    # SQLALCHEMY_DATABASE_URI = 'mysql://user:password@localhost:3306/walle?charset=utf8'
    SQLALCHEMY_DATABASE_URI = 'mysql+pymysql://{}:{}@{}:{}/{}?charset=utf8mb4'.format(
        SETTINGS["MYSQL_USER"], SETTINGS["MYSQL_PASSWORD"], SETTINGS["MYSQL_HOST"], SETTINGS["MYSQL_PORT"],
        SETTINGS["MYSQL_DATABASE"])
    # 阿里云RDS强制释放空闲连接导致经常报错 mysql server has gone way
    # 适当修改该参数即可 单位为秒
    # N秒不用的连接自动释放
    # SQLALCHEMY_POOL_RECYCLE = 30

    # 本地代码检出路径（用户查询分支, 编译, 打包） #TODO
    CODE_BASE = '/tmp/walle/codebase/'

    # 日志存储路径 @TODO
    # 默认为walle-web项目下logs, 可自定义路径, 需以 / 结尾
    # LOG_PATH = '/var/logs/walle/'
    LOG_PATH = os.path.join(Config.PROJECT_ROOT, 'logs')
    LOG_PATH_ERROR = os.path.join(LOG_PATH, 'error.log')
    LOG_PATH_INFO = os.path.join(LOG_PATH, 'info.log')
    LOG_FILE_MAX_BYTES = 100 * 1024 * 1024

    # 邮箱配置 @TODO
    MAIL_SERVER = 'smtp.exmail.qq.com'
    MAIL_PORT = 465
    MAIL_USE_SSL = True
    MAIL_USE_TLS = False
    MAIL_DEFAULT_SENDER = 'service@walle-web.io'
    MAIL_USERNAME = 'service@walle-web.io'
    MAIL_PASSWORD = 'Ki9y&3U82'

    # 登录cookie 防止退出浏览器重新登录
    COOKIE_ENABLE = False
