# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

from flask import request

from walle.api.api import SecurityResource
from walle.model.menu import MenuModel
from walle.model.role import RoleModel


class AccessAPI(SecurityResource):
    controller = 'access'

    """
    权限是以resource + method作为一个access

    """

    def get(self, access_id=None):
        """
        fetch access list or one access

        :return:
        """
        super(AccessAPI, self).get()
        return self.item(access_id) if access_id else self.list()

    def list(self):
        """
        fetch access list
        /access/

        :return:
        """

        access_model = MenuModel()
        access_list = access_model.list()
        return self.render_json(data=access_list)

    def item(self, access_id):
        """
        /access/<int:access_id>

        :param access_id:
        :return:
        """
        access_info = RoleModel().list(size=1000)
        data = MenuModel.query.all()
        list = [p.to_json() for p in data]
        return self.render_json(data=list)

    def post(self):
        """
        新增角色
        /access/

        :return:
        """
        super(AccessAPI, self).post()

        access_name = request.form.get('access_name', None)
        access_permissions_ids = request.form.get('access_ids', '')
        access_model = RoleModel()
        access_id = access_model.add(name=access_name, access_ids=access_permissions_ids)

        if not access_id:
            self.render_json(code=-1)
        return self.render_json(data=access_model.item())

    def put(self, access_id):
        """
        修改角色
        /access/<int:access_id>

        :param access_id:
        :return:
        """
        super(AccessAPI, self).put()

        access_name = request.form.get('access_name', None)
        access_ids = request.form.get('access_ids', '')

        if not access_name:
            return self.render_json(code=-1, message='access_name can not be empty')

        access_model = RoleModel(id=access_id)
        ret = access_model.update(name=access_name, access_ids=access_ids)
        return self.render_json(data=access_model.item())

    def delete(self, access_id):
        """
        删除一个角色
        /access/<int:access_id>

        :return:
        """
        super(AccessAPI, self).delete()

        access_model = RoleModel(id=access_id)
        ret = access_model.remove()

        return self.render_json(code=0)
