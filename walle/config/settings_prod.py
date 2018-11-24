# -*- coding: utf-8 -*-
"""
    walle-web
    Application configuration.

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-24 07:05:35
    :author: wushuiyong@walle-web.io
"""
from datetime import timedelta

import os
from walle.config.settings import Config


class ProdConfig(Config):
    """Development configuration."""

    HOST = 'admin.walle-web.io'
    PORT = 5000
    # SERVER_NAME = 'dev.admin.walle-web.io:5000'
    ENV = 'prod'
    DEBUG = False
    DB_NAME = 'walle_python'
    # Put the db file in project root
    WTF_CSRF_ENABLED = False
    DB_PATH = os.path.join(Config.PROJECT_ROOT, DB_NAME)
    SQLALCHEMY_DATABASE_URI = 'mysql://root:@localhost/walle_python'
    DEBUG_TB_ENABLED = False
    ASSETS_DEBUG = True  # Don't bundle/minify static assets
    CACHE_TYPE = 'simple'  # Can be "memcached", "redis", etc.
    # 设置session的保存时间。
    PERMANENT_SESSION_LIFETIME = timedelta(days=1)

    # 前端项目部署路径
    FE_PATH = os.path.abspath(Config.PROJECT_ROOT + '/../walle-fe/')
    AVATAR_PATH = 'avatar/'
    UPLOAD_AVATAR = FE_PATH + 'dist/' + AVATAR_PATH

    # email config
    MAIL_SERVER = 'smtp.exmail.qq.com'
    MAIL_PORT = 465
    MAIL_USE_SSL = True
    MAIL_USE_TLS = False
    MAIL_DEFAULT_SENDER = 'service@walle-web.io'
    MAIL_USERNAME = 'service@walle-web.io'
    MAIL_PASSWORD = 'Ki9y&3U82'

    LOG_PATH = os.path.join(Config.PROJECT_ROOT, 'logs')
    LOG_PATH_ERROR = os.path.join(LOG_PATH, 'error.log')
    LOG_PATH_INFO = os.path.join(LOG_PATH, 'info.log')
    LOG_PATH_DEBUG = os.path.join(LOG_PATH, 'debug.log')
    LOG_FILE_MAX_BYTES = 100 * 1024 * 1024

    # 轮转数量是 10 个
    LOG_FILE_BACKUP_COUNT = 10
    LOG_FORMAT = "%(asctime)s %(thread)d %(message)s"

    LOCAL_SERVER_HOST = '127.0.0.1'
    LOCAL_SERVER_USER = 'work'
    LOCAL_SERVER_PORT = 22

    SQLALCHEMY_ECHO = False
