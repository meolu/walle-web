# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""
from flask_login import login_required, current_user
from flask import request
from walle.form.group import GroupForm
from walle.model.user import MemberModel, UserModel
from walle.model.user import SpaceModel
from walle.model.tag import TagModel
from walle.api.api import SecurityResource
from flask import current_app
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
        size = float(request.args.get('size', 10))
        kw = request.values.get('kw', '')
        filter = {'name': {'like': kw}} if kw else {}
        space_model = SpaceModel()
        space_list, count = space_model.list(page=page, size=size, kw=kw)
        return self.list_json(list=space_list, count=count, enable_create=permission.enable_role(OWNER))

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

        ## mixin 版本
        group_model = TagModel().get_by_id(group_id)
        if not group_model:
            return self.render_json(code=-1)

        user_model = UserModel()
        user_info = user_model.fetch_by_uid(uids=group_model.users)

        group_info = group_model.to_dict()
        group_info['members'] = user_info
        group_info['users'] = len(user_info)
        group_info['group_name'] = group_info['name']
        group_info['group_id'] = group_info['id']
        return self.render_json(data=group_info)

    def post(self):
        """
        create group
        /group/

        :return:
        """
        super(GroupAPI, self).post()
        current_app.logger.info(request.form)
        current_app.logger.info(request.form.user_ids)

        form = GroupForm(request.form, csrf_enabled=False)
        if form.validate_on_submit():
            # user_ids = [int(uid) for uid in form.user_ids.data.split(',')]

            group_id = 0
            # group_new = MemberModel()
            # group_id = group_new.add(group_name=form.group_name.data, user_ids=user_ids)
            if not group_id:
                return self.render_json(code=-1)
            return self.render_json(data=group_new.item())
        else:
            return self.render_json(code=-1, message=form.errors)

    def put(self, group_id):
        """
        update group
        /group/<int:group_id>

        :return:
        """
        super(GroupAPI, self).put()

        form = GroupForm(request.form, csrf_enabled=False)
        form.set_group_id(group_id)
        if form.validate_on_submit():
            # pass
            # user_ids = [int(uid) for uid in form.user_ids.data.split(',')]
            import json
            current_app.logger.info(form.uid_roles)

            current_app.logger.info(json.loads(form.uid_roles))

            group_model = MemberModel(group_id=group_id)
            for uid_role in json.loads(form.uid_roles):
                uid_role['project_id'] = 0
                current_app.logger.info(uid_role)
                group_model.create_or_update(uid_role, uid_role)

            # group_model.update(group_id=group_id,
            #                    group_name=form.group_name.data,
            #                    user_ids=user_ids)
            return self.render_json(data=group_model.item())

        return self.render_json(code=-1, message=form.errors)

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
