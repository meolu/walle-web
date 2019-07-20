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
from walle.service.error import WalleError

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
                try:
                    remember = False
                    if current_app.config.get("COOKIE_ENABLE"):
                        remember = True
                    current_app.logger.info("remember me(记住我)功能是否开启,{}".format(remember))
                    login_user(user, remember=remember)
                    user.fresh_session()
                except WalleError as e:
                    return self.render_json(code=e.code, data=Code.code_msg[e.code])
                return self.render_json(data=current_user.to_json())

        return self.render_json(code=Code.error_pwd, data=form.errors)

    def logout(self):
        logout_user()
        return self.render_json()
