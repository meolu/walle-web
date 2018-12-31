# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-19 15:50:07
    :author: wushuiyong@walle-web.io
"""
try:
    from FlaskForm import FlaskForm  # Try Flask-WTF v0.13+
except ImportError:
    from flask_wtf import Form as FlaskForm  # Fallback to Flask-WTF v0.12 or older
import json

from flask import current_app
from walle.model.tag import TagModel
from walle.model.user import UserModel
from wtforms import StringField
from wtforms import validators, ValidationError


class GroupForm(FlaskForm):
    group_name = StringField('group_name', [validators.Length(min=1, max=100)])
    uid_roles = StringField('uid_roles', [validators.Length(min=1)])
    group_id = None

    def set_group_id(self, group_id):
        self.group_id = group_id

    def validate_user_ids(self, field):
        current_app.logger.info(field.data)
        self.uid_roles = json.loads(field.data)

        user_ids = [uid_role['user_id'] for uid_role in self.uid_roles]
        roles = [uid_role['role'] for uid_role in self.uid_roles]
        # TODO validator roles
        # current_app.logger.info(user_ids)
        if UserModel.query.filter(UserModel.id.in_(user_ids)).count() != len(user_ids):
            raise ValidationError('存在未记录的用户添加到用户组')

    def validate_group_name(self, field):
        env = TagModel.query.filter_by(name=field.data).filter_by(label='user_group').first()
        # 新建时,环境名不可与
        if env and env.id != self.group_id:
            raise ValidationError('该用户组已经配置过')

    def validate_members(self, field):
        pass
