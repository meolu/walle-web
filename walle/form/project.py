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
from walle.model.project import ProjectModel
from walle.service.notice import Notice
from wtforms import StringField
from wtforms import validators, ValidationError
from datetime import datetime


class ProjectForm(FlaskForm):
    name = StringField('name', [validators.Length(min=1, max=100)])
    environment_id = StringField('environment_id', [validators.Length(min=1, max=10)])
    space_id = StringField('space_id', [validators.Length(min=1, max=10)])
    status = StringField('status', [])
    excludes = StringField('excludes', [])
    is_include = StringField('excludes', [])
    master = StringField('master', [])
    server_ids = StringField('server_ids', [validators.Length(min=1)])
    keep_version_num = StringField('keep_version_num', [])

    target_root = StringField('target_root', [validators.Length(min=1, max=200)])
    target_releases = StringField('target_releases', [validators.Length(min=1, max=200)])

    task_vars = StringField('task_vars', [])
    prev_deploy = StringField('prev_deploy', [])
    post_deploy = StringField('post_deploy', [])
    prev_release = StringField('prev_release', [])
    post_release = StringField('post_release', [])

    repo_url = StringField('repo_url', [validators.Length(min=1, max=200)])
    repo_username = StringField('repo_username', [])
    repo_password = StringField('repo_password', [])
    repo_mode = StringField('repo_mode', [validators.Length(min=1, max=50)])
    notice_type = StringField('notice_type', [])
    notice_hook = StringField('notice_hook', [])
    task_audit = StringField('task_audit', [])

    id = None

    def set_id(self, id):
        self.id = id

    def form2dict(self):
        return {
            'name': self.name.data.replace('"', '').replace("'", ''),
            'user_id': current_user.id,

            'status': self.status.data if self.status.data else 1,
            'master': self.master.data if self.master.data else '',
            'environment_id': self.environment_id.data if self.environment_id.data else '',
            'space_id': self.space_id.data if self.space_id.data else current_user.space_id(),
            'excludes': self.excludes.data if self.excludes.data else '',
            'is_include': self.is_include.data,
            'server_ids': self.server_ids.data if self.server_ids.data else '',
            'keep_version_num': self.keep_version_num.data if self.keep_version_num.data else 5,

            'target_root': self.target_root.data.rstrip('/') if self.target_root.data else '',
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

            'notice_type': self.notice_type.data if self.notice_type.data in [Notice.by_email,
                                                                              Notice.by_dingding] else '',
            'notice_hook': self.notice_hook.data if self.notice_hook.data else '',
            'task_audit': self.task_audit.data if self.task_audit.data else 0,
            'created_at': datetime.now(),
            'updated_at': datetime.now(),

        }
