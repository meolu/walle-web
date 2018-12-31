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

from wtforms import StringField
from wtforms import validators


class RoleAdd(FlaskForm):
    name = StringField('Email Address', [validators.Length(min=6, max=35), validators.InputRequired()])
    # password = SelectField('Password', [validators.Length(min=6, max=35))
