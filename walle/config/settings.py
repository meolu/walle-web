# -*- coding: utf-8 -*-
"""Application configuration."""
import os
from datetime import timedelta

PLACEHOLDER_SECRET_KEYS = frozenset((
    'changeme',
    'change-me',
    'default',
    'password',
    'secret',
    'secret-key',
    'secret_key',
    'secretkey',
    'your-secret-key',
    'your-secret-key-here',
))


class Config(object):
    """Base configuration."""
    VERSION = '2.0.1'

    SECRET_KEY = os.environ.get('WALLE_SECRET')
    APP_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), os.pardir))
    PROJECT_ROOT = os.path.abspath(os.path.join(APP_DIR, os.pardir))
    BCRYPT_LOG_ROUNDS = 13
    ASSETS_DEBUG = False
    WTF_CSRF_ENABLED = False
    DEBUG_TB_ENABLED = False
    DEBUG_TB_INTERCEPT_REDIRECTS = False

    # Can be "memcached", "redis", etc.
    CACHE_TYPE = 'simple'
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    SQLALCHEMY_COMMIT_ON_TEARDOWN = True

    LOGIN_DISABLED = False
    # 设置session的保存时间。
    PERMANENT_SESSION_LIFETIME = timedelta(days=1)

    # 前端项目部署路径
    FE_PATH = os.path.abspath(PROJECT_ROOT + '/fe/') + '/'
    AVATAR_PATH = '/avatar/'
    UPLOAD_AVATAR = FE_PATH + AVATAR_PATH

    # 邮箱配置
    MAIL_SERVER = 'smtp.exmail.qq.com'
    MAIL_PORT = 465
    MAIL_USE_SSL = True
    MAIL_USE_TLS = False
    MAIL_DEFAULT_SENDER = 'service@walle-web.io'
    MAIL_USERNAME = 'service@walle-web.io'
    MAIL_PASSWORD = os.environ.get('WALLE_MAIL_PASSWORD')

    # 日志
    LOG_PATH = os.path.join(PROJECT_ROOT, 'logs')
    LOG_PATH_ERROR = os.path.join(LOG_PATH, 'error.log')
    LOG_PATH_INFO = os.path.join(LOG_PATH, 'info.log')
    LOG_FILE_MAX_BYTES = 100 * 1024 * 1024

    # 轮转数量是 10 个
    LOG_FILE_BACKUP_COUNT = 10
    LOG_FORMAT = "%(asctime)s %(thread)d %(message)s"

    # 登录cookie 防止退出浏览器重新登录
    COOKIE_ENABLE = False

    @staticmethod
    def validate_secret_key(secret):
        """Reject missing, short or placeholder signing secrets."""
        if isinstance(secret, bytes):
            secret = secret.decode('utf-8')
        if not secret or not secret.strip():
            raise RuntimeError(
                'SECRET_KEY is missing. Set WALLE_SECRET to a unique random '
                'value of at least 32 characters before starting Walle.')
        if (secret.strip().lower() in PLACEHOLDER_SECRET_KEYS or
                len(secret.encode('utf-8')) < 32):
            raise RuntimeError(
                'SECRET_KEY must be at least 32 bytes and not a placeholder. '
                'Set WALLE_SECRET to a unique random value before starting '
                'Walle.')
        return secret
