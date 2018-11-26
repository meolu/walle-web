# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

from flask import request
from walle.api.api import SecurityResource
from walle.model.role import RoleModel


class RoleAPI(SecurityResource):
    """
    角色模型跟gitlab一样,分别是超管,空间所有者,项目管理员,开发者,访客
    """

    def get(self):
        """
        fetch role list
        /role/

        :return:
        """
        role_list, count = RoleModel.list()
        return self.list_json(list=role_list, count=count)
