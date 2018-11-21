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

from walle.model.user import SpaceModel


class SpaceForm(Form):
    name = TextField('name', [validators.Length(min=1, max=100)])
    user_id = TextField('user_id', [validators.Length(min=1, max=100)])
    status = TextField('status', [])
    id = None

    def set_id(self, id):
        self.id = id

    def validate_name(self, field):
        space = SpaceModel.query.filter_by(name=field.data).first()
        # 新建时,环境名不可与
        if space and space.id != self.id:
            raise ValidationError('该Space已重名')

    def form2dict(self):
        return {
            'name': self.name.data if self.name.data else '',
            # TODO g.uid
            'user_id': self.user_id.data if self.user_id.data else '',
            'status': 1,
        }