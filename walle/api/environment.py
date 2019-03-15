# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

from flask import request
from walle.api.api import SecurityResource
from walle.form.environment import EnvironmentForm
from walle.model.environment import EnvironmentModel
from walle.service.extensions import permission
from walle.service.rbac.role import *


class EnvironmentAPI(SecurityResource):

    def get(self, env_id=None):
        """
        fetch environment list or one item
        /environment/<int:env_id>

        :return:
        """
        super(EnvironmentAPI, self).get()

        return self.item(env_id) if env_id else self.list()

    def list(self):
        """
        fetch environment list

        :return:
        """

        page = int(request.args.get('page', 0))
        page = page - 1 if page else 0
        size = int(request.args.get('size', 10))
        kw = request.values.get('kw', '')

        table = [
            {
                'key': 'tag',
                'value': ['线上', '测试'],
                'sort': 0
            }
        ]

        env_model = EnvironmentModel()

        env_list, count = env_model.list(page=page, size=size, kw=kw, space_id=self.space_id)
        return self.list_json(list=env_list, count=count, table=table,
                              enable_create=permission.role_upper_master() and current_user.role != SUPER)

    def item(self, env_id):
        """
        获取某个用户组

        :param env_id:
        :return:
        """

        env_model = EnvironmentModel(id=env_id)
        env_info = env_model.item()
        if not env_info:
            return self.render_json(code=-1)
        return self.render_json(data=env_info)

    @permission.upper_master
    def post(self):
        """
        create a environment
        /environment/

        :return:
        """
        super(EnvironmentAPI, self).post()

        form = EnvironmentForm(request.form, csrf=False)
        if form.validate_on_submit():
            env = EnvironmentModel()
            data = form.form2dict()
            env_new = env.add(data)
            if not env_new:
                return self.render_json(code=-1)
            return self.render_json(data=env_new)
        else:
            return self.render_error(code=Code.form_error, message=form.errors)

    @permission.upper_master
    def put(self, env_id):
        """
        update environment
        /environment/<int:env_id>

        :return:
        """
        super(EnvironmentAPI, self).put()

        form = EnvironmentForm(request.form, csrf=False)
        form.set_env_id(env_id)
        if form.validate_on_submit():
            env = EnvironmentModel(id=env_id)

            data = form.form2dict()

            ret = env.update(env_name=form.env_name.data, status=form.status.data)
            return self.render_json(data=env.item())
        else:
            return self.render_error(code=Code.form_error, message=form.errors)

    @permission.upper_master
    def delete(self, env_id):
        """
        remove an environment
        /environment/<int:env_id>

        :return:
        """
        super(EnvironmentAPI, self).delete()

        env_model = EnvironmentModel(id=env_id)
        env_model.remove(env_id)

        return self.render_json(message='')
