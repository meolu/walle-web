# -*- coding: utf-8 -*-
"""
    walle-web
    Application configuration.
    注意: 带了 @TODO 的地方可能需要你的调整

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-24 07:05:35
    :author: wushuiyong@walle-web.io
"""

from datetime import timedelta

import os
from walle.config.settings import Config


class ProdConfig(Config):
    """Production configuration."""

    # 服务启动 @TODO
    # 跟hosts, nginx配置一致
    HOST = 'admin.walle-web.io'
    PORT = 5000

    ENV = 'prod'
    DEBUG = False
    PROPAGATE_EXCEPTIONS = True
    WTF_CSRF_ENABLED = False
    DEBUG_TB_ENABLED = False
    CACHE_TYPE = 'simple'

    # 数据库设置 @TODO
    SQLALCHEMY_DATABASE_URI = 'mysql://user:password@localhost/walle'

    # 设置session的保存时间。
    PERMANENT_SESSION_LIFETIME = timedelta(days=1)

    # 前端项目部署路径
    FE_PATH = os.path.abspath(Config.PROJECT_ROOT + '/../walle-fe/') + '/'
    AVATAR_PATH = 'avatar/'
    UPLOAD_AVATAR = FE_PATH + '/dist/' + AVATAR_PATH

    # 本地代码检出路径（用户查询分支, 编译, 打包） #TODO
    CODE_BASE = '/tmp/walle/codebase/'

    # 邮箱配置 @TODO
    MAIL_SERVER = 'smtp.exmail.qq.com'
    MAIL_PORT = 465
    MAIL_USE_SSL = True
    MAIL_USE_TLS = False
    MAIL_DEFAULT_SENDER = 'service@walle-web.io'
    MAIL_USERNAME = 'service@walle-web.io'
    MAIL_PASSWORD = 'Ki9y&3U82'

    # 日志 @TODO
    LOG_PATH = os.path.join(Config.PROJECT_ROOT, 'logs')
    LOG_PATH_ERROR = os.path.join(LOG_PATH, 'error.log')
    LOG_PATH_INFO = os.path.join(LOG_PATH, 'info.log')
    LOG_PATH_DEBUG = os.path.join(LOG_PATH, 'debug.log')
    LOG_FILE_MAX_BYTES = 100 * 1024 * 1024

    # 轮转数量是 10 个
    LOG_FILE_BACKUP_COUNT = 10
    LOG_FORMAT = "%(asctime)s %(thread)d %(message)s"

    # 宿主机（walle部署所在的机器以及用户） @TODO
    LOCAL_SERVER_HOST = '127.0.0.1'
    LOCAL_SERVER_USER = 'work'
    LOCAL_SERVER_PORT = 22

    SQLALCHEMY_ECHO = False
