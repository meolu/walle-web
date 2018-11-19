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

from walle.model.deploy import ServerModel


class ServerForm(Form):
    name = TextField('name', [validators.Length(min=1, max=100)])
    host = TextField('host', [validators.Length(min=1, max=100)])
    id = None

    def set_id(self, id):
        self.id = id

    def validate_name(self, field):
        server = ServerModel.query.filter_by(name=field.data).first()
        # 新建时,环境名不可与
        if server and server.id != self.id:
            raise ValidationError('该Server已重名')
