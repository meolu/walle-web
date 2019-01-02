# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2018-11-04 22:08:28
    :author: wushuiyong@walle-web.io
"""
from functools import wraps

from flask import session
from flask_login import login_required, current_user
from walle.service.code import Code
from walle.service.error import WalleError
from flask import current_app

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

    def upper_owner(self, func):
        '''
        角色高于owner
        @param func:
        @return:
        '''
        @wraps(func)
        @login_required
        def decorator(*args, **kwargs):
            if self.role_upper_owner():
                return func(*args, **kwargs)

            raise WalleError(Code.not_allow)

        return decorator

    def upper_master(self, func):
        '''
        角色高于master
        @param func:
        @return:
        '''
        @wraps(func)
        @login_required
        def decorator(*args, **kwargs):
            if self.role_upper_master():
                return func(*args, **kwargs)

            raise WalleError(Code.not_allow)

        return decorator

    def upper_developer(self, func):
        '''
        角色高于developer
        @param func:
        @return:
        '''
        @wraps(func)
        @login_required
        def decorator(*args, **kwargs):
            if self.role_upper_developer():
                return func(*args, **kwargs)

            raise WalleError(Code.not_allow)

        return decorator

    def upper_reporter(self, func):
        '''
        角色高于reporter
        @param func:
        @return:
        '''
        @wraps(func)
        @login_required
        def decorator(*args, **kwargs):
            if self.role_upper_reporter():
                return func(*args, **kwargs)

            raise WalleError(Code.not_allow)

        return decorator

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
        return current_user.id == uid

    def role_upper_owner(self, role=None):
        '''
        项目project的角色role比developer级别更高, 传参, 不传则
        空间space的角色role比developer级别更高, 不用传, 默认从session中取
        :param role:
        :return:
        '''
        return self.role_upper(OWNER, role)

    def role_upper_master(self, role=None):
        '''
        项目project的角色role比developer级别更高, 传参, 不传则
        空间space的角色role比developer级别更高, 不用传, 默认从session中取
        :param role:
        :return:
        '''
        return self.role_upper(MASTER, role)

    def role_upper_developer(self, role=None):
        '''
        项目project的角色role比developer级别更高, 传参, 不传则
        空间space的角色role比developer级别更高, 不用传, 默认从session中取
        :param role:
        :return:
        '''
        return self.role_upper(DEVELOPER, role)

    def role_upper_reporter(self, role=None):
        '''
        项目project的角色role比developer级别更高, 传参, 不传则
        空间space的角色role比developer级别更高, 不用传, 默认从session中取
        :param role:
        :return:
        '''
        return self.role_upper(REPORT, role)

    def role_upper(self, role_standard, role_upper=None):
        '''
        当前角色 > 数据项角色
        :param role:
        :return:
        '''
        if current_user.role == SUPER:
            return True

        current_role = session['space_info']['role']
        return self.compare_role(role_standard, [current_role, role_upper])

    def compare_role(self, role_low, role_high):
        if not isinstance(role_high, (list, tuple)):
            role_high = [role_high]

        if role_low not in ROLE_ACCESS:
            return False

        for role in role_high:
            if role not in ROLE_ACCESS:
                continue

            if ROLE_ACCESS[role] > ROLE_ACCESS[role_low]:
                return True

        return False
