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
from datetime import timedelta

class TestConfig(Config):
    """Test configuration."""

    ENV = 'test'
    TESTING = True
    DEBUG = True

    # 服务启动 @TODO
    # HOST 修改为与 nginx server_name 一致.
    # 后续在web hooks与通知中用到此域名.
    HOST = 'admin.walle-web.io'
    PORT = 5000
    # https True, http False
    SSL = False

    # 数据库设置 @TODO
    SQLALCHEMY_DATABASE_URI = 'sqlite://'

    # 本地代码检出路径（用户查询分支, 编译, 打包） #TODO
    CODE_BASE = '/tmp/walle/codebase/'
