# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

from flask import request
from walle.api.api import SecurityResource
from walle.model.record import RecordModel
from walle.service.deployer import Deployer


class DeployAPI(SecurityResource):

    def get(self, task_id=None):
        """
        fetch deploy list or one item
        /deploy/<int:env_id>

        :return:
        """
        super(DeployAPI, self).get()

