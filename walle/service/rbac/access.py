# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-06-11 15:40:38
    :author: wushuiyong@walle-web.io
"""
import logging
from functools import wraps

from flask import current_app
from flask_login import current_user

class Access:

    def __init__(self):
        pass

    @staticmethod
    def is_login():
        # return True
        current_app.logger.info(current_user.is_authenticated)
        return current_user.is_authenticated

    @staticmethod
    def is_allow(action, controller, module=None):
        # return True
        current_resource = Access.resource(action, controller, module)
        # _role_delete
        return True
        # if current_user.is_authenticated:
        #     user_has_resource = current_user.fetch_access_list_by_role_id(current_user.role_id)
        # else:
        #     user_has_resource = []
        # logging.error(current_resource)
        # logging.error(user_has_resource)
        # return current_resource in user_has_resource

    @staticmethod
    def resource(action, controller, module=None):
        return "{}_{}_{}".format(module, controller, str(action))

