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
from flask_simpleldap import LDAP
from datetime import datetime
from werkzeug.security import generate_password_hash

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

            if current_app.config['LDAP']:
                ldap = LDAP(current_app)
                if form.password.data == '':
                    userbind = None
                else:
                    userbind = ldap.bind_user(form.email.data, form.password.data)
            else:
                ldap = current_app.config['LDAP']
                userbind = None

            if ldap:
                if userbind:
                    user = UserModel.query.filter_by(email=form.email.data).first()
                    if user is not None:
                        login_user(user)
                        user.fresh_session()
                        return self.render_json(data=current_user.to_json())
                    else:
                        # ldap验证成功，取信息入库
                        ldap_user = ldap.get_object_details(form.email.data)
                        user_info = {
                            'username': ldap_user['displayName'][0].decode(),
                            'password': generate_password_hash(form.password.data),
                            'email': form.email.data,
                            'role': '',
                            'created_at': datetime.now(),
                            'updated_at': datetime.now(),
                        }
                        user = UserModel().add(user_info)
                        login_user(user)
                        user.fresh_session()
                        return self.render_json(data=current_user.to_json())
                else:
                    return self.render_json(code=Code.error_pwd, data=form.errors)
            else:
                user = UserModel.query.filter_by(email=form.email.data).first()
                if user is not None and user.verify_password(form.password.data):
                    login_user(user)
                    user.fresh_session()
                    return self.render_json(data=current_user.to_json())

        return self.render_json(code=Code.error_pwd, data=form.errors)

    def logout(self):
        logout_user()
        return self.render_json()
