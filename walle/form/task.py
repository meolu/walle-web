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
from flask_login import current_user
from flask_wtf import Form
from walle.model.project import ProjectModel
from walle.model.task import TaskModel
from wtforms import TextField, IntegerField
from wtforms import validators


class TaskForm(Form):
    name = TextField('name', [validators.Length(min=1)])
    project_id = IntegerField('project_id', [validators.NumberRange(min=1)])
    servers = TextField('servers', [validators.Length(min=1)])
    commit_id = TextField('commit_id', [])
    status = IntegerField('status', [])
    # TODO 应该增加一个tag/branch其一必填
    tag = TextField('tag', [])
    branch = TextField('branch', [])
    file_transmission_mode = IntegerField('file_transmission_mode', [])
    file_list = TextField('file_list', [])

    id = None

    def set_id(self, id):
        self.id = id

    def form2dict(self):
        project_info = ProjectModel(id=self.project_id.data).item()
        task_status = TaskModel.status_new if project_info['task_audit'] == ProjectModel.task_audit_true else TaskModel.status_pass
        return {
            'name': self.name.data if self.name.data else '',
            # todo
            'user_id': current_user.id,
            'project_id': self.project_id.data,
            # todo default value
            'action': 0,
            'status': task_status,
            'link_id': '',
            'ex_link_id': '',
            'servers': self.servers.data if self.servers.data else '',
            'commit_id': self.commit_id.data if self.commit_id.data else '',
            'tag': self.tag.data if self.tag.data else '',
            'branch': self.branch.data if self.branch.data else '',
            'file_transmission_mode': self.file_transmission_mode.data if self.file_transmission_mode.data else 0,
            'file_list': self.file_list.data if self.file_list.data else '',
            'enable_rollback': 1,

        }
