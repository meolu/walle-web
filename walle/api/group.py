# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""
import json

from flask import request, current_app
from walle.api.api import SecurityResource
from walle.form.group import GroupForm
from walle.model.member import MemberModel
from walle.model.space import SpaceModel
from walle.model.tag import TagModel
from walle.service.extensions import permission
from walle.service.rbac.role import *


class GroupAPI(SecurityResource):
    def get(self, group_id=None):
        """
        用户组列表
        /group/

        :return:
        """
        super(GroupAPI, self).get()

        return self.item(group_id) if group_id else self.list()

    def list(self):
        """
        用户组列表
        /group/

        :return:
        """
        page = int(request.args.get('page', 0))
        page = page - 1 if page else 0
        size = int(request.args.get('size', 10))
        kw = request.values.get('kw', '')
        space_model = SpaceModel()
        space_list, count = space_model.list(page=page, size=size, kw=kw)
        return self.list_json(list=space_list, count=count, enable_create=permission.role_upper_owner())

        group_model, count = SpaceModel().query_paginate(page=page, limit=size, filter_name_dict=filter)
        groups = []
        for group_info in group_model:
            group_sub = MemberModel.query \
                .filter_by(group_id=group_info.id) \
                .count()

            group_info = group_info.to_json()
            group_info['users'] = group_sub
            group_info['group_id'] = group_info['id']
            group_info['group_name'] = group_info['name']
            groups.append(group_info)
        return self.list_json(list=groups, count=count)

    def item(self, group_id):
        """
        获取某个用户组
        /group/<int:group_id>

        :param group_id:
        :return:
        """
        ## sqlalchemy版本
        group_model = MemberModel()
        group = group_model.members(group_id=group_id)
        if group:
            return self.render_json(data=group)
        return self.render_json(code=-1)

    def put(self, group_id):
        """
        update group
        /group/<int:group_id>

        :return:
        """
        super(GroupAPI, self).put()

        form = GroupForm(request.form, csrf=False)
        form.set_group_id(group_id)
        if form.validate_on_submit():
            # pass
            # user_ids = [int(uid) for uid in form.user_ids.data.split(',')]
            current_app.logger.info(form.uid_roles)

            current_app.logger.info(json.loads(form.uid_roles))

            group_model = MemberModel(group_id=group_id)
            for uid_role in json.loads(form.uid_roles):
                uid_role['project_id'] = 0
                current_app.logger.info(uid_role)
                group_model.create_or_update(uid_role, uid_role)

            return self.render_json(data=group_model.item())

        return self.render_error(code=Code.form_error, message=form.errors)

    def delete(self, group_id):
        """
        /group/<int:group_id>

        :return:
        """
        super(GroupAPI, self).delete()

        group_model = MemberModel()
        tag_model = TagModel()
        tag_model.remove(group_id)
        group_model.remove(group_id)

        return self.render_json(message='')
