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
from walle.model.space import SpaceModel
from wtforms import StringField
from wtforms import validators, ValidationError
from datetime import datetime


class SpaceForm(FlaskForm):
    name = StringField('name', [validators.Length(min=1, max=100)])
    user_id = StringField('user_id', [validators.Length(min=1, max=100)])
    status = StringField('status', [])
    id = None

    def set_id(self, id):
        self.id = id

    def validate_name(self, field):
        filters = {
            SpaceModel.status.notin_([SpaceModel.status_remove]),
            SpaceModel.name == field.data
        }
        space = SpaceModel.query.filter(*filters).first()
        # 新建时,环境名不可与
        if space and space.id != self.id:
            raise ValidationError('该Space已重名')

    def form2dict(self):
        return {
            'name': self.name.data if self.name.data else '',
            'user_id': self.user_id.data if self.user_id.data else '',
            'status': 1,
            'created_at': datetime.now(),
            'updated_at': datetime.now(),

        }
