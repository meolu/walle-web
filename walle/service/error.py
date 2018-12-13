# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-17 21:42:23
    :author: wushuiyong@walle-web.io
"""
from flask import current_app, jsonify
from walle.service.code import Code


class WalleError(Exception):

    # 默认的返回码
    code = Code.unlogin

    message = None

    def __init__(self, code, message=None):
        Exception.__init__(self)

        if code is not None:
            self.code = code
        if message is not None:
            self.message = message

    def render_error(self):
        if self.code not in Code.code_msg:
            current_app.logger.error('unknown code %s' % (self.code))

        current_app.logger.error(self, exc_info=1)
        if self.code in Code.code_msg:
            self.message = Code.code_msg[self.code]

        return jsonify({
            'code': self.code,
            'message': self.message,
            'data': None,
        })