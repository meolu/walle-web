# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-24 07:12:13
    :author: wushuiyong@walle-web.io
"""
from datetime import datetime

from sqlalchemy import String, Integer, Text, DateTime
from walle import model
from walle.model.database import SurrogatePK, db, Model
from walle.model.user import UserModel
from walle.service.extensions import permission
from walle.service.rbac.role import *


# 项目配置表
class ProjectModel(SurrogatePK, Model):
    # 表的名字:
    __tablename__ = 'projects'
    current_time = datetime.now
    status_close = 0
    status_open = 1

    task_audit_true = 1
    task_audit_false = 0
    repo_mode_branch = 'branch'
    repo_mode_tag = 'tag'

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    user_id = db.Column(Integer)
    name = db.Column(String(100))
    environment_id = db.Column(Integer)
    space_id = db.Column(Integer)
    status = db.Column(Integer)
    master = db.Column(String(100))
    version = db.Column(String(40))
    excludes = db.Column(Text)
    is_include = db.Column(Integer)
    target_root = db.Column(String(200))
    target_releases = db.Column(String(200))
    server_ids = db.Column(Text)
    task_vars = db.Column(Text)
    prev_deploy = db.Column(Text)
    post_deploy = db.Column(Text)
    prev_release = db.Column(Text)
    post_release = db.Column(Text)
    keep_version_num = db.Column(Integer)
    repo_url = db.Column(String(200))
    repo_username = db.Column(String(50))
    repo_password = db.Column(String(50))
    repo_mode = db.Column(String(50))
    repo_type = db.Column(String(10))
    notice_type = db.Column(String(10))
    notice_hook = db.Column(Text)
    task_audit = db.Column(Integer)

    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

    def list(self, page=0, size=10, kw=None, space_id=None, environment_id=None):
        """
        获取分页列表
        :param page:
        :param size:
        :return:
        """
        query = self.query.filter(ProjectModel.status.notin_([self.status_remove]))
        if kw:
            query = query.filter(ProjectModel.name.like('%' + kw + '%'))

        # 关联 environments
        EnvironmentModel = model.environment.EnvironmentModel
        query = query.join(EnvironmentModel, EnvironmentModel.id == ProjectModel.environment_id)
        query = query.filter(EnvironmentModel.status.notin_([self.status_remove]))

        # 关联 spaces
        SpaceModel = model.space.SpaceModel
        query = query.join(SpaceModel, SpaceModel.id == ProjectModel.space_id)
        query = query.filter(SpaceModel.status.notin_([self.status_remove]))

        if environment_id:
            query = query.filter(ProjectModel.environment_id == environment_id)

        if space_id:
            query = query.filter(ProjectModel.space_id == space_id)

        query = query.add_columns(EnvironmentModel.name, SpaceModel.name)
        count = query.count()

        data = query.order_by(ProjectModel.id.desc()).offset(int(size) * int(page)).limit(size).all()

        project_list = []
        for p in data:
            item = p[0].to_json()
            item['environment_name'] = p[1]
            item['space_name'] = p[2]
            project_list.append(item)

        return project_list, count

    def item(self, id=None):
        """
        获取单条记录
        :param role_id:
        :return:
        """
        id = id if id else self.id
        data = self.query.filter(ProjectModel.status.notin_([self.status_remove])).filter_by(id=id).first()
        if not data:
            return []

        project_info = data.to_json()

        ServerModel = model.server.ServerModel
        server_ids = project_info['server_ids']
        project_info['servers_info'] = ServerModel.fetch_by_id(list(map(int, server_ids.split(','))))
        return project_info

    def add(self, *args, **kwargs):
        data = dict(*args)
        project = ProjectModel(**data)

        db.session.add(project)
        db.session.commit()

        return project.to_json()

    def update(self, *args, **kwargs):
        update_data = dict(*args)
        return super(ProjectModel, self).update(**update_data)

    def remove(self, role_id=None):
        """

        :param role_id:
        :return:
        """
        role_id = role_id if role_id else self.id
        ProjectModel.query.filter_by(id=role_id).update({'status': self.status_remove})

        ret = db.session.commit()

        return ret

    def to_json(self):
        item = {
            'id': self.id,
            'user_id': self.user_id,
            'name': self.name,
            'environment_id': self.environment_id,
            'space_id': self.space_id,
            'status': self.status,
            'master': UserModel.fetch_by_uid(self.master.split(',')) if self.master else '',
            'version': self.version,
            'excludes': self.excludes,
            'is_include': self.is_include,
            'target_root': self.target_root,
            'target_releases': self.target_releases,
            'server_ids': self.server_ids,
            'task_vars': self.task_vars,
            'prev_deploy': self.prev_deploy,
            'post_deploy': self.post_deploy,
            'prev_release': self.prev_release,
            'post_release': self.post_release,
            'keep_version_num': self.keep_version_num,
            'repo_url': self.repo_url,
            'repo_username': self.repo_username,
            'repo_password': self.repo_password,
            'repo_mode': self.repo_mode,
            'repo_type': self.repo_type,
            'notice_type': self.notice_type,
            'notice_hook': self.notice_hook,
            'task_audit': self.task_audit,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
        item.update(self.enable())
        return item

    def enable(self):
        return {
            'enable_view': True,
            'enable_update': permission.role_upper_developer(),
            'enable_delete': permission.enable_uid(self.user_id) or permission.role_upper_developer(),
            'enable_create': False,
            'enable_online': False,
            'enable_audit': False,
            'enable_block': False,
        }
