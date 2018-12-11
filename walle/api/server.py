# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

from flask import request
from walle.api.api import SecurityResource
from walle.form.server import ServerForm
from walle.model.server import ServerModel
from walle.service.extensions import permission
from walle.service.rbac.role import *


class ServerAPI(SecurityResource):

    @permission.upper_developer
    def get(self, id=None):
        """
        fetch environment list or one item
        /environment/<int:env_id>

        :return:
        """
        super(ServerAPI, self).get()

        return self.item(id) if id else self.list()

    @permission.upper_developer
    def list(self):
        """
        fetch environment list

        :return:
        """
        page = int(request.args.get('page', 0))
        page = page - 1 if page else 0
        size = int(request.args.get('size', 10))
        kw = request.values.get('kw', '')

        server_model = ServerModel()
        server_list, count = server_model.list(page=page, size=size, kw=kw)
        return self.list_json(list=server_list, count=count, enable_create=permission.role_upper_developer())

    def item(self, id):
        """
        获取某个用户组

        :param id:
        :return:
        """

        server_model = ServerModel(id=id)
        server_info = server_model.item()
        if not server_info:
            return self.render_json(code=-1)
        return self.render_json(data=server_info)

    def post(self):
        """
        create a environment
        /environment/

        :return:
        """
        super(ServerAPI, self).post()

        form = ServerForm(request.form, csrf_enabled=False)
        if form.validate_on_submit():
            server_new = ServerModel()
            id = server_new.add(name=form.name.data, host=form.host.data)
            if not id:
                return self.render_json(code=-1)

            return self.render_json(data=server_new.item(id))
        else:
            return self.render_json(Code.form_error, message=form.errors)

    @permission.upper_developer
    def put(self, id):
        """
        update environment
        /environment/<int:id>

        :return:
        """
        super(ServerAPI, self).put()

        form = ServerForm(request.form, csrf_enabled=False)
        form.set_id(id)
        if form.validate_on_submit():
            server = ServerModel(id=id)
            ret = server.update(name=form.name.data, host=form.host.data)
            return self.render_json(data=server.item())
        else:
            return self.render_error(code=Code.form_error, message=form.errors)

    @permission.upper_developer
    def delete(self, id):
        """
        remove an environment
        /environment/<int:id>

        :return:
        """
        super(ServerAPI, self).delete()

        server_model = ServerModel(id=id)
        server_model.remove(id)

        return self.render_json(message='')
