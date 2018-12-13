# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-09-06 20:20:33
    :author: wushuiyong@walle-web.io
"""
from flask import current_app
from flask_login import current_user, login_required
from flask_socketio import emit, join_room, Namespace
from walle.model.record import RecordModel
from walle.model.task import TaskModel
from walle.service.deployer import Deployer

class WalleSocketIO(Namespace):
    namespace, room, app = None, None, None

    task_info = None

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
            emit('close', {'event': 'disconnect', 'data': {}}, room=self.room)
        join_room(room=self.room)

        self.task_info = TaskModel(id=self.room).item()
        emit('construct', {'event': 'connect', 'data': self.task_info}, room=self.room)

    def on_deploy(self, message):
        self.task_info = TaskModel(id=self.room).item()
        if self.task_info['status'] in [TaskModel.status_pass, TaskModel.status_fail]:
            wi = Deployer(task_id=self.room, console=True)
            ret = wi.walle_deploy()
        else:
            emit('console', {'event': 'forbidden', 'data': self.task_info}, room=self.room)

    def on_branches(self, message):
        wi = Deployer(task_id=self.room)
        try:
            branches = wi.list_branch()
            emit('branches', {'event': 'branches', 'data': branches}, room=self.room)
        except Exception as e:
            emit('branches', {'event': 'error', 'data': {'message': e.message}}, room=self.room)

    def on_tags(self, message):
        wi = Deployer(task_id=self.room)
        try:
            tags = wi.list_tag()
            emit('tags', {'event': 'tags', 'data': tags}, room=self.room)
        except Exception as e:
            emit('tags', {'event': 'error', 'data': {'message': e.message}}, room=self.room)

    def on_commits(self, message):
        wi = Deployer(task_id=self.room)
        if 'branch' not in message:
            emit('commits', {'event': 'error', 'data': {'message': 'invalid branch'}}, room=self.room)
        else:
            try:
                commits = wi.list_commit(message['branch'])
                emit('commits', {'event': 'commits', 'data': commits}, room=self.room)
            except Exception as e:
                emit('commits', {'event': 'error', 'data': {'message': e.message}}, room=self.room)

    def on_ping(self, message):
        current_app.logger.info(message)
        import time
        emit('pong',
             {'event': 'ping:pong', 'data': {'time': time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(time.time()))}},
             room=self.room)

    def on_logs(self, message):
        current_app.logger.info(message)
        self.logs(task=self.room)

    def logs(self, task):
        emit('console', {'event': 'console', 'data': {'task': task}}, room=task)
        logs = RecordModel().fetch(task_id=task)
        for log in logs:
            log = RecordModel.logs(**log)
            emit('console', {'event': 'console', 'data': log}, room=self.room)
