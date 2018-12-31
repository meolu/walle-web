# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""


from flask import request, abort, current_app
from flask_login import current_user
from flask_login import login_user, logout_user
from walle.api.api import ApiResource
from walle.form.user import LoginForm
from walle.model.user import UserModel
from walle.service.code import Code

class PassportAPI(ApiResource):
    actions = ['login', 'logout']

    def post(self, action):
        """
        user login
        /passport/

        :return:
        """

        if action in self.actions:
            self_action = getattr(self, action.lower(), None)
            return self_action()
        else:
            abort(404)

    def login(self):
        """
        user login
        /passport/

        :return:
        """
        form = LoginForm(request.form, csrf=False)
        if form.validate_on_submit():
            user = UserModel.query.filter_by(email=form.email.data).first()

            if user is not None and user.verify_password(form.password.data):
                login_user(user)
                user.fresh_session()
                return self.render_json(data=current_user.to_json())

        return self.render_json(code=Code.error_pwd, data=form.errors)

    def logout(self):
        logout_user()
        return self.render_json()
