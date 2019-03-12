# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

import json

from flask import current_app
from flask import request, abort
from walle.api.api import SecurityResource
from walle.form.space import SpaceForm
from walle.model.member import MemberModel
from walle.model.space import SpaceModel
from walle.model.user import UserModel
from walle.service.extensions import permission
from walle.service.rbac.role import *


class SpaceAPI(SecurityResource):
    actions = ['members', 'item', 'list', 'member', 'switch']

    @permission.upper_developer
    def get(self, space_id=None, action=None):
        """
        fetch space list or one item
        /space/<int:space_id>

        :return:
        """
        super(SpaceAPI, self).get()
        if action is None:
            action = 'item' if space_id else 'list'

        if action in self.actions:
            self_action = getattr(self, action.lower(), None)
            return self_action(space_id)
        else:
            abort(404)

    def list(self, space_id=None):
        """
        fetch space list

        :return:
        """
        page = int(request.args.get('page', 0))
        page = page - 1 if page else 0
        size = int(request.args.get('size', 10))
        kw = request.values.get('kw', '')

        space_model = SpaceModel()
        space_list, count = space_model.list(page=page, size=size, kw=kw)
        return self.list_json(list=space_list, count=count, enable_create=permission.role_upper_owner())

    def item(self, space_id):
        """
        获取某个用户组

        :param id:
        :return:
        """

        space_model = SpaceModel(id=space_id)
        space_info = space_model.item()
        if not space_info:
            return self.render_json(code=-1)
        return self.render_json(data=space_info)

    @permission.upper_master
    def post(self):
        """
        create a space
        /environment/

        :return:
        """
        super(SpaceAPI, self).post()

        form = SpaceForm(request.form, csrf=False)
        # return self.render_json(code=-1, data = form.form2dict())
        if form.validate_on_submit():
            # create space
            space_new = SpaceModel()
            data = form.form2dict()
            id = space_new.add(data)
            if not id:
                return self.render_json(code=-1)

            current_app.logger.info(request.json)
            # create group
            data['role'] = OWNER
            members = [data]
            MemberModel(group_id=id).update_group(members=members)
            return self.render_json(data=space_new.item())
        else:
            return self.render_error(code=Code.form_error, message=form.errors)

    def put(self, space_id, action=None):
        """
        update environment
        /environment/<int:id>

        :return:
        """
        super(SpaceAPI, self).put()
        if action is None:
            return self.update(space_id)

        if action in self.actions:
            self_action = getattr(self, action.lower(), None)
            return self_action(space_id)
        else:
            abort(404)

    @permission.upper_master
    def update(self, space_id):
        form = SpaceForm(request.form, csrf=False)
        form.set_id(space_id)
        if form.validate_on_submit():
            space = SpaceModel().get_by_id(space_id)
            data = form.form2dict()
            current_app.logger.info(data)

            # a new type to update a model
            ret = space.update(data)
            # create group
            member = {"user_id": data['user_id'], "role": OWNER}
            members = []
            if 'members' in request.form:
                members = json.loads(request.form['members'])
                members.append(member)
            MemberModel(group_id=space_id).update_group(members=members)
            return self.render_json(data=space.item())
        else:
            return self.render_error(code=Code.form_error, message=form.errors)

    @permission.upper_master
    def delete(self, space_id):
        """
        remove an environment
        /environment/<int:id>

        :return:
        """
        super(SpaceAPI, self).delete()

        space_model = SpaceModel(id=space_id)
        space_model.remove(space_id)

        return self.render_json(message='')

    def switch(self, space_id):
        session['space_id'] = space_id

        # TODO
        current_user.last_space = space_id
        current_user.save()
        UserModel.fresh_session()
        return self.render_json()

    def member(self, space_id):
        '''
        查看成员
        @param space_id:
        @return:
        '''
        space_id = session['space_id']
        user_id = request.form['user_id']
        role = request.form['role']

        members = MemberModel(group_id=space_id).member(user_id=user_id, role=role, group_id=space_id)
        return self.render_json(data=members)

    @permission.upper_developer
    def members(self, space_id):
        '''
        更新组成员
        @param space_id:
        @return:
        '''
        page = int(request.args.get('page', 1))
        page = page - 1 if page else 0
        size = int(request.args.get('size', 10))
        kw = request.values.get('kw', '')
        members, count, user_ids = MemberModel(group_id=space_id).members(page=page, size=size, kw=kw)
        return self.list_json(list=members, count=count, enable_create=permission.role_upper_master())
