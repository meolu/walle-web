# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-26 16:06:44
    :author: wushuiyong@walle-web.io
"""

from datetime import datetime

from sqlalchemy import String, Integer, DateTime
from walle.model.database import SurrogatePK
from walle.model.database import db, Model
from walle.model.user import UserModel
from walle.service.extensions import permission
from walle.service.rbac.role import *


class RoleModel(object):
    _role_super = 'SUPER'

    _role_owner = 'OWNER'

    _role_master = 'MASTER'

    _role_developer = 'DEVELOPER'

    _role_reporter = 'REPORTER'

    @classmethod
    def list(cls):
        roles = [
            {'id': cls._role_super, 'name': '超级管理员'},
            {'id': cls._role_owner, 'name': '空间所有者'},
            {'id': cls._role_master, 'name': '项目管理员'},
            {'id': cls._role_developer, 'name': '开发者'},
            {'id': cls._role_reporter, 'name': '访客'},
        ]
        return roles, len(roles)

    @classmethod
    def item(cls, role_id):
        return None

    @classmethod
    def menu_url(cls, url):
        if url == '/':
            return url
        prefix = 'admin' if current_user.role == SUPER else session['space_info']['name']

        return '/' + prefix + url

