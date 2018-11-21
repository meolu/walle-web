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

from walle.model.deploy import ProjectModel


class ProjectForm(Form):
    name = TextField('name', [validators.Length(min=1, max=100)])
    environment_id = TextField('environment_id', [validators.Length(min=1, max=10)])
    space_id = TextField('space_id', [validators.Length(min=1, max=10)])
    status = TextField('status', [])
    excludes = TextField('excludes', [])
    master = TextField('master', [])
    server_ids = TextField('server_ids', [validators.Length(min=1)])
    keep_version_num = TextField('keep_version_num', [])

    target_user = TextField('target_user', [validators.Length(min=1, max=50)])
    target_port = TextField('target_port', [validators.Length(min=1, max=50)])
    target_root = TextField('target_root', [validators.Length(min=1, max=200)])
    target_releases = TextField('target_releases', [validators.Length(min=1, max=200)])

    task_vars = TextField('task_vars', [])
    prev_deploy = TextField('prev_deploy', [])
    post_deploy = TextField('post_deploy', [])
    prev_release = TextField('prev_release', [])
    post_release = TextField('post_release', [])

    repo_url = TextField('repo_url', [validators.Length(min=1, max=200)])
    repo_username = TextField('repo_username', [])
    repo_password = TextField('repo_password', [])
    repo_mode = TextField('repo_mode', [validators.Length(min=1, max=50)])
    notice_type = TextField('notice_type', [])
    notice_hook = TextField('notice_hook', [])
    enable_audit = TextField('enable_audit', [])

    id = None

    def set_id(self, id):
        self.id = id

    def validate_name(self, field):
        server = ProjectModel.query.filter_by(name=field.data).first()
        # 新建时,项目名不可与
        if server and server.id != self.id:
            raise ValidationError('该项目已重名')

    def form2dict(self):
        return {
            'name': self.name.data if self.name.data else '',
            # TODO g.uid
            'user_id': 1,

            'status': self.status.data if self.status.data else 0,
            'master': self.master.data if self.master.data else '',
            'environment_id': self.environment_id.data if self.environment_id.data else '',
            'space_id': self.space_id.data if self.space_id.data else '',
            'excludes': self.excludes.data if self.excludes.data else '',
            'server_ids': self.server_ids.data if self.server_ids.data else '',
            'keep_version_num': self.keep_version_num.data if self.keep_version_num.data else 5,

            'target_user': self.target_user.data if self.target_user.data else '',
            'target_port': self.target_port.data if self.target_port.data else '',
            'target_root': self.target_root.data if self.target_root.data else '',
            'target_releases': self.target_releases.data if self.target_releases.data else '',

            'task_vars': self.task_vars.data if self.task_vars.data else '',
            'prev_deploy': self.prev_deploy.data if self.prev_deploy.data else '',
            'post_deploy': self.post_deploy.data if self.post_deploy.data else '',
            'prev_release': self.prev_release.data if self.prev_release.data else '',
            'post_release': self.post_release.data if self.post_release.data else '',

            'repo_url': self.repo_url.data if self.repo_url.data else '',
            'repo_username': self.repo_username.data if self.repo_username.data else '',
            'repo_password': self.repo_password.data if self.repo_password.data else '',
            'repo_mode': self.repo_mode.data if self.repo_mode.data else '',

            'notice_type': self.notice_type.data if self.notice_type.data else '',
            'notice_hook': self.notice_hook.data if self.notice_hook.data else '',
            'enable_audit': self.enable_audit.data if self.enable_audit.data else 0,
        }
