# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-09-06 20:20:33
    :author: wushuiyong@walle-web.io
"""
from flask import current_app
from flask_login import current_user
from flask_socketio import emit, join_room
from walle.model.deploy import TaskRecordModel


class WalleSocketIO(object):
    app = None
    room = None

    def __init__(self, room):
        self.room = room

    def init_app(self, app):
        self.app = app

    def logs(self):
        emit('console', {'event': 'task:console', 'data': {'task': self.room}}, room=self.room)
        logs = TaskRecordModel().fetch(task_id=self.room)
        for log in logs:
            # current_app.logger.info(log)
            log = TaskRecordModel.logs(**log)
            # current_app.logger.info(self.room)
            emit('console', {'event': 'task:console', 'data': log}, room=self.room)


