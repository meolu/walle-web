# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-06-14 15:53:46
    :author: wushuiyong@walle-web.io
"""

import logging
from walle.service.extensions import login_manager
from walle.model.user import UserModel
from walle.model.user import RoleModel
from walle.model.user import MenuModel


@login_manager.user_loader
def load_user(user_id):
    logging.error(user_id)
    user = UserModel.query.get(user_id)
    role = RoleModel().item(user.role_id)
    access = MenuModel().fetch_access_list_by_role_id(user.role_id)
    logging.error(access)
    # logging.error(RoleModel.query.get(user.role_id).access_ids)
    # logging.error(role['access_ids'].split(','))
    # logging.error(UserModel.query.get(user_id))
    return UserModel.query.get(user_id)
