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
from flask_login import current_user
from walle.model.environment import EnvironmentModel
from wtforms import StringField
from wtforms import validators, ValidationError
from datetime import datetime


class EnvironmentForm(FlaskForm):
    env_name = StringField('env_name', [validators.Length(min=1, max=100)])
    status = StringField('status', [])
    space_id = None
    env_id = None

    def set_env_id(self, env_id):
        self.env_id = env_id

    def set_space_id(self, space_id):
        self.space_id = space_id

    def validate_env_name(self, field):
        filters = {
            EnvironmentModel.status.notin_([EnvironmentModel.status_remove]),
            EnvironmentModel.name == field.data,
            EnvironmentModel.space_id == current_user.space_id(),
        }
        env = EnvironmentModel.query.filter(*filters).first()
        # 新建时,环境名不可与
        if env and env.id != self.env_id:
            raise ValidationError('该环境已经配置过')

    def validate_status(self, field):
        if field.data and int(field.data) not in [1, 2]:
            raise ValidationError('非法的状态')

    def form2dict(self):
        return {
            'name': self.env_name.data,
            'space_id': current_user.space_id(),
            'status': 1,
            'created_at': datetime.now(),
            'updated_at': datetime.now(),

        }
