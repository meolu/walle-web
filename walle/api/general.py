# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

import os
from flask import request, abort, session, current_app
from flask_login import current_user, login_required
from walle.api.api import SecurityResource
from walle.model.record import RecordModel
from walle.model.menu import MenuModel
from walle.model.user import UserModel
from walle.service import emails
from walle.service.deployer import Deployer
from walle.service.rbac.role import *
from werkzeug.utils import secure_filename
from walle.service.extensions import permission


class GeneralAPI(SecurityResource):
    actions = ['menu', 'websocket']

    # TODO 更细致的检查
    @permission.upper_reporter
    def get(self, action):
        """
        fetch role list or one role

        :return:
        """

        if action in self.actions:
            self_action = getattr(self, action.lower(), None)
            return self_action()
        else:
            abort(404)

    def post(self, action):
        """
        fetch role list or one role

        :return:
        """
        if action == 'avatar':
            return self.avater()

    def menu(self):
        role = SUPER if current_user.role == SUPER else ROLE_ACCESS[session['space_info']['role']]
        user = UserModel(id=current_user.id).item()
        menu = MenuModel().menu(role=role)
        space = {
            'current': '',
            'available': '',
        }
        # TODO
        # 超管不需要展示空间列表
        if current_user.role != SUPER:
            space = {
                'current': session['space_info'],
                'available': session['space_list'],
            }
        data = {
            'user': user,
            'menu': menu,
            'space': space,
        }
        return self.render_json(data=data)

    def mail(self):
        ret = emails.send_email('wushuiyong@renrenche.com', 'email from service@walle-web.io', 'xxxxxxx', 'yyyyyyy')
        return self.render_json(data={
            'avatar': 'emails.send_email',
            'done': ret,
        })

    def websocket(self, task_id=None):
        task_id = 12
        wi = Deployer(task_id)
        ret = wi.walle_deploy()
        record = RecordModel().fetch(task_id)
        return self.render_json(data={
            'command': ret,
            'record': record,
        })
