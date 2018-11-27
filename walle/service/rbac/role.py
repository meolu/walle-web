# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2018-11-04 22:08:28
    :author: wushuiyong@walle-web.io
"""
from flask import current_app, session
from flask_login import login_required, current_user
from functools import wraps
from walle.service.code import Code
from walle.service.error import WalleError

GUEST = 'GUEST'
REPORT = 'REPORT'
DEVELOPER = 'DEVELOPER'
MASTER = 'MASTER'
OWNER = 'OWNER'
SUPER = 'SUPER'

ACCESS_ROLE = {
    '10': GUEST,
    '20': REPORT,
    '30': DEVELOPER,
    '40': MASTER,
    '50': OWNER,
    '60': SUPER,
}

ROLE_ACCESS = {
    'GUEST': '10',
    'REPORT': '20',
    'DEVELOPER': '30',
    'MASTER': '40',
    'OWNER': '50',
    'SUPER': '60',
}

class Permission():

    app = None

    def __init__(self, app=None):
        if app:
            self.init_app(app)

    def init_app(self, app):
        self.app = app

    def gte_develop_or_uid(self, func):
        @wraps(func)
        @login_required
        def decorator(*args, **kwargs):
            current_app.logger.info('============== gte_develop_or_uid.decorator ======')
            if self.is_gte_develop_or_uid(current_user.id):
                current_app.logger.info('============== gte_develop_or_uid.if ======')
                return func(*args, **kwargs)

            raise WalleError(Code.not_allow)

        return decorator

    def is_gte_develop_or_uid(self, uid=None):
        if uid is None:
            uid = current_user.id

        if self.enable_uid(uid) or self.enable_role(DEVELOPER):
            return True

        return False

    @staticmethod
    def list_enable(self, list, access_level):
        current_role = OWNER
        access_level = {
            'create': OWNER,
            'update': MASTER,
            'delete': MASTER,
            'online': DEVELOPER,
            'audit': MASTER,
            'block': DEVELOPER,
        }
        # 1 uid == current_uid && access_level >= current_role
        #       all true
        # uid, project_id, space_id

        return {
            'enable_create': OWNER,
            'enable_update': MASTER,
            'enable_delete': MASTER,
            'enable_online': DEVELOPER,
            'enable_audit': MASTER,
            'enable_block': DEVELOPER,
        }
        pass

    # @classmethod
    def enable_uid(self, uid):
        '''
        当前登录用户 == 数据用户
        :param uid:
        :return:
        '''
        # TODO
        current_app.logger.info('uid %s current_uid %s %s', uid, current_user.id, current_user.id==uid)
        return current_user.id == uid

    # @classmethod
    def enable_role(self, role):
        '''
        当前角色 >= 数据项角色
        :param role:
        :return:
        '''
        if current_user.role == SUPER:
            return True

        # TODO about project/task
        current_role = session['space_info']['role']
        current_app.logger.info(current_role)
        current_app.logger.info(role)
        return self.compare_role(current_role, role)

    # @classmethod
    def compare_role(self, role_high, role_low):
        if role_high not in ROLE_ACCESS or role_low not in ROLE_ACCESS:
            # TODO 也可以抛出
            return False

        return ROLE_ACCESS[role_high] > ROLE_ACCESS[role_low]
