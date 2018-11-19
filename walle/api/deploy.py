# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

from flask import request
from walle.api.api import SecurityResource
from walle.model.deploy import TaskRecordModel
from walle.service.deployer import Deployer
from walle.service.websocket import WSHandler


class DeployAPI(SecurityResource):
    def get(self, task_id=None):
        """
        fetch environment list or one item
        /environment/<int:env_id>

        :return:
        """
        super(DeployAPI, self).get()

    # def get(self, method):
    #     """
    #     fetch role list or one role
    #
    #     :return:
    #     """
    #     if method == 'menu':
    #         return self.menu()
    #     elif method == 'mail':
    #         return self.mail()
    #     elif method == 'walle':
    #         return self.walless()

    def post(self):
        """
        fetch role list or one role

        :return:
        """
        super(DeployAPI, self).post()

        task_id = request.form['task_id']
        if not task_id or not task_id.isdigit():
            return self.render_json(code=-1)
        wi = Deployer(task_id, websocket=WSHandler)
        ret = wi.walle_deploy()
        record = TaskRecordModel().fetch(task_id)
        return self.render_json(data={
            'command': '',
            'record': record,
        })
