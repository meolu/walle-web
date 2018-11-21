# -*- coding: utf-8 -*-
"""Application configuration."""
import os
from walle.config.settings import Config


class ProdConfig(Config):
    """Production configuration."""

    ENV = 'prod'
    DEBUG = False
    SQLALCHEMY_DATABASE_URI = 'postgresql://localhost/example'  # TODO: Change me
    DEBUG_TB_ENABLED = False  # Disable Debug toolbar


    # 前端项目部署路径
    FE_PATH = '/Users/wushuiyong/workspace/meolu/walle-fe/'
    UPLOAD_AVATER = FE_PATH + 'dist/avater/'

