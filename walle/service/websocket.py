# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-09-06 20:20:33
    :author: wushuiyong@walle-web.io
"""
from flask import current_app
from flask_login import current_user
from flask_socketio import emit, join_room, Namespace
from walle.model.deploy import TaskRecordModel


class WalleSocketIO(Namespace):
    namespace, room, app = None, None, None

    def __init__(self, namespace, room=None, app=None):
        super(WalleSocketIO, self).__init__(namespace=namespace)
        self.room = room
        self.app = app

    def init_app(self, app):
        self.app = app

    def on_connect(self):
        pass

    def on_open(self, message):
        current_app.logger.info(message)
        self.room = message['task']
        if not current_user.is_authenticated:
            emit('close', {'event': 'pusher:disconnect', 'data': {}}, room=self.room)
        join_room(room=self.room)

        emit('construct', {'event': 'pusher:connect', 'data': {}}, room=self.room)

    def on_deploy(self, message):
        emit('console', {'event': 'task:console', 'data': {}}, room=self.room)
        from walle.service.deployer import Deployer
        wi = Deployer(task_id=self.room)
        ret = wi.walle_deploy()

    def on_logs(self, message):
        current_app.logger.info(message)
        self.logs(task=self.room)

    def logs(self, task):
        emit('console', {'event': 'task:console', 'data': {'task': task}}, room=task)
        logs = TaskRecordModel().fetch(task_id=task)
        for log in logs:
            log = TaskRecordModel.logs(**log)
            emit('console', {'event': 'task:console', 'data': log}, room=task)
