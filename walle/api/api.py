# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-06-14 16:00:23
    :author: wushuiyong@walle-web.io
"""

from flask import jsonify, abort, current_app, request
from flask_restful import Resource
from walle.service.rbac.access import Access as AccessRbac
from functools import wraps
from walle.service.code import Code
from flask import current_app, session
from flask_login import current_user

class ApiResource(Resource):
    module = None
    controller = None
    actions = None
    action = None

    # TODO 权限验证
    def __init__(self):
        pass

    @staticmethod
    def render_json(code=0, message='', data=[]):
        return ApiResource.json(code=code, message=message, data=data)

    @staticmethod
    def json(code=0, message=None, data=[]):
        if not Code.code_msg.has_key(code):
            current_app.logger.error('unkown code %s' % (code))

        if Code.code_msg.has_key(code) and not message:
            message = Code.code_msg[code]

        return jsonify({
            'code': code,
            'message': message,
            'data': data,
        })

    @staticmethod
    def list_json(list, count, table={}, code=0, message='', enable_create=False):
        return ApiResource.render_json(data={'list': list, 'count': count, 'table': table, 'enable_create': enable_create},
                                       code=code,
                                       message=message)


class SecurityResource(ApiResource):
    module = None
    controller = None
    action = None

    # @login_required
    def get(self, *args, **kwargs):
        self.action = 'get'
        current_app.logger.info('========= SecurityResource =======')


        return self.validator()

    # @login_required
    def delete(self, *args, **kwargs):
        self.action = 'delete'
        is_allow = AccessRbac.is_allow(action=self.action, controller=self.controller)
        if not is_allow:
            self.render_json(code=403, message=u'无操作权限')
            # abort(403)
            pass
        pass

    # @login_required
    def put(self, *args, **kwargs):
        self.action = 'put'
        is_allow = AccessRbac.is_allow(action=self.action, controller=self.controller)
        if not is_allow:
            self.render_json(code=403, message=u'无操作权限')
            # abort(403)
            pass
        pass

    # @login_required
    def post(self, *args, **kwargs):
        """
        # @login_required
        :param args:
        :param kwargs:
        :return:
        """
        self.action = 'post'
        return self.validator()

    def validator(self):
        if not AccessRbac.is_login():
            return self.render_json(code=1000, message=u'请先登录')

        if not AccessRbac.is_allow(action=self.action, controller=self.controller):
            return self.render_json(code=1001, message=u'无操作权限')


    @staticmethod
    def is_super(func):
        @wraps(func)
        def is_enable(*args, **kwargs):
            if current_user.role_info.name <> 'super':
                return ApiResource.render_json(code=403, message=u'无操作权限')
            current_app.logger.info("user is login: %s" % (current_user.is_authenticated))
            current_app.logger.info("args: %s kwargs: %s" % (args, kwargs))
            return func(*args, **kwargs)

        return is_enable

    @staticmethod
    def is_master(func):
        @wraps(func)
        def is_enable(*args, **kwargs):
            if current_user.role_info.name not in ['super', 'master']:
                return ApiResource.render_json(code=403, message=u'无操作权限')
            current_app.logger.info("user is login: %s" % (current_user.is_authenticated))
            current_app.logger.info("args: %s kwargs: %s" % (args, kwargs))
            return func(*args, **kwargs)

        return is_enable


class Base(Resource):
    def get(self):
        """
        fetch role list or one role

        :return:
        """
        return 'walle-web 2.0'
