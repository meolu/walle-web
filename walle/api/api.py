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
from walle.service.rbac.role import SUPER


class ApiResource(Resource):
    module = None
    controller = None
    actions = None
    action = None

    def __init__(self):
        pass

    @staticmethod
    def render_json(code=0, message='', data=[]):
        return ApiResource.json(code=code, message=message, data=data)

    @staticmethod
    def render_error(code=0, message='', data=[]):
        if code == Code.form_error:
            msg = ''
            for err_key in message:
                current_app.logger.info(err_key)
                current_app.logger.info('.'.join(message[err_key]))
                msg = msg + "%s: %s。 " % (err_key, '.'.join(message[err_key]))
            message = msg

        return ApiResource.json(code=code, message=message, data=data)


    @staticmethod
    def json(code=0, message=None, data=[]):
        if code and code not in Code.code_msg:
            current_app.logger.error('unknown code %s' % (code))

        if code in Code.code_msg and not message:
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

    space_id = None
    SCOPED_ID_PARAMS = {
        'project': set(['project_id']),
        'environment': set(['env_id']),
        'server': set(['id']),
        'task': set(['task_id']),
        'deploy': set(['task_id']),
        'group': set(['group_id']),
        'repo': set(),
    }
    GLOBAL_ID_ENDPOINTS = set(['access', 'space', 'user'])

    def __init__(self):
        if current_user.is_authenticated:
            current_user.fresh_session()
            if current_user.role == SUPER:
                self.space_id = None
            else:
                self.space_id = session['space_id']

    def dispatch_request(self, *args, **kwargs):
        """Authorize scoped resource identifiers before endpoint dispatch."""
        if current_user.is_authenticated and current_user.role != SUPER:
            if not self._target_in_space(kwargs):
                return self.render_json(code=-1)

        return super(SecurityResource, self).dispatch_request(*args, **kwargs)

    def _target_in_space(self, kwargs):
        endpoint = request.endpoint.rsplit('.', 1)[-1]
        id_params = set([
            name for name in kwargs if name == 'id' or name.endswith('_id')])
        if endpoint in self.SCOPED_ID_PARAMS:
            if not id_params.issubset(self.SCOPED_ID_PARAMS[endpoint]):
                return False
        elif id_params and endpoint not in self.GLOBAL_ID_ENDPOINTS:
            return False

        if endpoint == 'project':
            return self._project_in_space(kwargs.get('project_id'))
        if endpoint == 'environment':
            return self._environment_in_space(kwargs.get('env_id'))
        if endpoint in ['task', 'deploy']:
            task_id = kwargs.get('task_id')
            if task_id is not None:
                from walle.model.project import ProjectModel
                from walle.model.task import TaskModel
                return TaskModel.query.join(
                    ProjectModel, TaskModel.project_id == ProjectModel.id) \
                    .filter(TaskModel.id == task_id,
                            ProjectModel.space_id == self.space_id) \
                    .first() is not None
        if endpoint == 'server':
            server_id = kwargs.get('id')
            if server_id is not None:
                from walle.model.project import ProjectModel
                server_ref = ',%s,' % server_id
                references = ProjectModel.query.filter(
                    (',' + ProjectModel.server_ids + ',').like(
                        '%%%s%%' % server_ref))
                own_reference = references.filter_by(
                    space_id=self.space_id).first() is not None
                foreign_reference = references.filter(
                    ProjectModel.space_id != self.space_id).first() is not None
                if request.method in ['PUT', 'DELETE']:
                    return not foreign_reference
                return own_reference or not foreign_reference
        if endpoint == 'group':
            group_id = kwargs.get('group_id')
            if group_id is not None:
                return str(group_id) == str(self.space_id)
        if endpoint == 'repo':
            return self._project_in_space(request.args.get('project_id'))

        return True

    def _project_in_space(self, project_id):
        if project_id is None or current_user.role == SUPER:
            return True
        from walle.model.project import ProjectModel
        return ProjectModel.query.filter_by(
            id=project_id, space_id=self.space_id).first() is not None

    def _environment_in_space(self, environment_id):
        if environment_id is None or current_user.role == SUPER:
            return True
        from walle.model.environment import EnvironmentModel
        return EnvironmentModel.query.filter_by(
            id=environment_id, space_id=self.space_id).first() is not None

    # @login_required
    def get(self, *args, **kwargs):
        self.action = 'get'

        return self.validator()

    # @login_required
    def delete(self, *args, **kwargs):
        self.action = 'delete'
        is_allow = AccessRbac.is_allow(action=self.action, controller=self.controller)
        if not is_allow:
            self.render_json(code=403, message='无操作权限')
            # abort(403)
            pass
        pass

    # @login_required
    def put(self, *args, **kwargs):
        self.action = 'put'
        is_allow = AccessRbac.is_allow(action=self.action, controller=self.controller)
        if not is_allow:
            self.render_json(code=403, message='无操作权限')
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
            return self.render_json(code=1000, message='请先登录')

        if not AccessRbac.is_allow(action=self.action, controller=self.controller):
            return self.render_json(code=1001, message='无操作权限')


    @staticmethod
    def is_super(func):
        @wraps(func)
        def is_enable(*args, **kwargs):
            if current_user.role_info.name != 'super':
                return ApiResource.render_json(code=403, message='无操作权限')
            current_app.logger.info("user is login: %s" % (current_user.is_authenticated))
            current_app.logger.info("args: %s kwargs: %s" % (args, kwargs))
            return func(*args, **kwargs)

        return is_enable

    @staticmethod
    def is_master(func):
        @wraps(func)
        def is_enable(*args, **kwargs):
            if current_user.role_info.name not in ['super', 'master']:
                return ApiResource.render_json(code=403, message='无操作权限')
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
