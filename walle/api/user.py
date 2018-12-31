# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""
import os
from flask import request, current_app, abort
from walle.api.api import SecurityResource
from walle.form.user import UserUpdateForm, RegistrationForm
from walle.model.member import MemberModel
from walle.model.user import UserModel
from walle.service import emails
from walle.service.extensions import permission
from walle.service.rbac.role import *
from werkzeug.security import generate_password_hash


class UserAPI(SecurityResource):
    actions = ['avatar', 'block', 'active']

    @permission.upper_developer
    def get(self, user_id=None, method=None):
        """
        fetch user list or one user
        /user/<int:user_id>

        :return:
        """
        super(UserAPI, self).get()

        return self.item(user_id) if user_id else self.list()

    def list(self):
        """
        fetch user list or one user

        :return:
        """
        page = int(request.args.get('page', 0))
        page = page - 1 if page else 0
        size = int(request.args.get('size', 10))
        space_id = int(request.args.get('space_id', 0))
        kw = request.values.get('kw', '')

        user_ids = []
        if current_user.role != SUPER and space_id:
            members = MemberModel(group_id=current_user.last_space).members()
            user_ids = members['user_ids']

        user_model = UserModel()
        user_list, count = user_model.list(uids=user_ids, page=page, size=size, space_id=space_id, kw=kw)
        filters = {
            'username': ['线上', '线下'],
            'status': ['正常', '禁用']
        }
        return self.list_json(list=user_list, count=count, table=self.table(filters),
                              enable_create=permission.role_upper_master())

    def item(self, user_id):
        """
        获取某个用户

        :param user_id:
        :return:
        """

        user_info = UserModel(id=user_id).item()
        if not user_info:
            return self.render_json(code=-1)
        return self.render_json(data=user_info)

    def post(self, user_id=None, action=None):
        """
        create user
        /user/

        :return:
        """
        super(UserAPI, self).post()

        if action and action == 'avatar':
            return self.avatar(user_id)

        return self.create_user()

    @permission.upper_developer
    def create_user(self):
        form = RegistrationForm(request.form, csrf=False)
        if form.validate_on_submit():
            user_info = form.form2dict()
            # add user
            user = UserModel().add(user_info)
            # send an email
            message = """Hi, %s
                    <br> <br>Welcome to walle, it cost a lot of time and lock to meet you, enjoy it : )
                    <br><br>name: %s<br>password: %s""" \
                              % (user.username, user.email, form.password.data)
            emails.send_email(user.email, 'Welcome to walle', message, '')

            return self.render_json(data=user.item(user_id=user.id))
        return self.render_error(code=Code.form_error, message=form.errors)

    @permission.upper_developer
    def put(self, user_id, action=None):
        """
        edit user
        /user/<int:user_id>

        :return:
        """
        super(UserAPI, self).put()

        if action:
            if action in self.actions:
                self_action = getattr(self, action.lower(), None)
                return self_action(user_id=user_id)
            else:
                abort(404)

        form = UserUpdateForm(request.form, csrf=False)
        if form.validate_on_submit():
            user = UserModel(id=user_id)
            user.update_name_pwd(username=form.username.data, password=form.password.data)
            return self.render_json(data=user.item())

        return self.render_error(code=Code.form_error, message=form.errors)

    @permission.upper_developer
    def delete(self, user_id):
        """
        remove a user with his group relation
        /user/<int:user_id>

        :param user_id:
        :return:
        """
        super(UserAPI, self).delete()

        UserModel(id=user_id).remove()
        MemberModel().remove(user_id=user_id)
        return self.render_json(message='')

    @staticmethod
    def table(filter={}):
        table = {
            'username': {
                'sort': 0
            },
            'email': {
                'sort': 0
            },
            'status': {
                'sort': 0
            },
            'role_name': {
                'sort': 0
            },
        }
        ret = []
        for (key, value) in list(table.items()):
            value['key'] = key
            if key in filter:
                value['value'] = filter[key]
            else:
                value['value'] = []
            ret.append(value)
        return ret

    def avatar(self, user_id):
        random = generate_password_hash(str(user_id))
        fname = random[-10:] + '.jpg'
        current_app.logger.info(fname)

        f = request.files['avatar']
        # todo rename to uid relation
        # fname = secure_filename(f.filename)
        # TODO try
        ret = f.save(os.path.join(current_app.config['UPLOAD_AVATAR'], fname))
        user = UserModel.query.get(user_id)
        user.avatar = fname
        user.save()
        return self.render_json(data={
            'avatar': UserModel.avatar_url(user.avatar),
        })

    def block(self, user_id):
        user = UserModel(id=user_id)
        user.block_active(UserModel.status_blocked)
        return self.render_json(data=user.item())

    def active(self, user_id):
        user = UserModel(id=user_id)
        user.block_active(UserModel.status_active)
        return self.render_json(data=user.item())
