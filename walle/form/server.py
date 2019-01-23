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
from walle.model.server import ServerModel
from wtforms import StringField
from wtforms import validators, ValidationError
from datetime import datetime


class ServerForm(FlaskForm):
    name = StringField('name', [validators.Length(min=1, max=100)])
    host = StringField('host', [validators.Length(min=1, max=100)])
    user = StringField('user', [validators.Length(min=1, max=100)])
    port = StringField('port', [validators.Length(min=1, max=100)])
    id = None

    def set_id(self, id):
        self.id = id

    def validate_name(self, field):
        server = ServerModel.query.filter_by(name=field.data).first()
        # 新建时,环境名不可与
        if server and server.id != self.id:
            raise ValidationError('该Server已重名')

    def form2dict(self):
        return {
            'name': self.name.data if self.name.data else '',
            'host': self.host.data,
            'user': self.user.data,
            'port': self.port.data if self.port.data else 22,
            'status': 1,
            'created_at': datetime.now(),
            'updated_at': datetime.now(),

        }
