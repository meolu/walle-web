# -*- coding: utf-8 -*-
"""Application configuration."""
import os


class Config(object):
    """Base configuration."""

    SECRET_KEY = os.environ.get('WALLE_SECRET', 'secret-key')  # TODO: Change me
    APP_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), os.pardir))  # This directory
    PROJECT_ROOT = os.path.abspath(os.path.join(APP_DIR, os.pardir))
    BCRYPT_LOG_ROUNDS = 13
    ASSETS_DEBUG = False
    DEBUG_TB_ENABLED = False  # Disable Debug toolbar
    DEBUG_TB_INTERCEPT_REDIRECTS = False
    CACHE_TYPE = 'simple'  # Can be "memcached", "redis", etc.
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    SQLALCHEMY_COMMIT_ON_TEARDOWN = True

    AVATAR_PATH = 'avatar/'

    LOGIN_DISABLED = False


    LOCAL_SERVER_HOST = '127.0.0.1'
    LOCAL_SERVER_USER = 'wushuiyong'
    LOCAL_SERVER_PORT = 22

    CODE_BASE = '/tmp/walle/codebase/'
