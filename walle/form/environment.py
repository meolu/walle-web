# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-19 15:50:07
    :author: wushuiyong@walle-web.io
"""
try:
    from flask_wtf import FlaskForm  # Try Flask-WTF v0.13+
except ImportError:
    from flask_wtf import Form as FlaskForm  # Fallback to Flask-WTF v0.12 or older
from flask_wtf import Form
from wtforms import TextField
from wtforms import validators, ValidationError

from walle.model.deploy import EnvironmentModel


class EnvironmentForm(Form):
    env_name = TextField('env_name', [validators.Length(min=1, max=100)])
    status = TextField('status', [validators.Length(min=0, max=10)])
    env_id = None

    def set_env_id(self, env_id):
        self.env_id = env_id

    def validate_env_name(self, field):
        env = EnvironmentModel.query.filter_by(name=field.data).first()
        # 新建时,环境名不可与
        if env and env.id != self.env_id:
            raise ValidationError('该环境已经配置过')

    def validate_status(self, field):
        if field.data and int(field.data) not in [1, 2]:
            raise ValidationError('非法的状态')
