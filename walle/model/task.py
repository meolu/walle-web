# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-24 06:22:04
    :author: wushuiyong@walle-web.io
"""
from datetime import datetime

from sqlalchemy import String, Integer, DateTime, Text
from walle import model
from walle.model.database import db, Model, SurrogatePK
from walle.service.extensions import permission
from walle.service.rbac.role import *


# 上线单
class TaskModel(SurrogatePK, Model):
    __tablename__ = 'tasks'
    current_time = datetime.now
    # 状态：0新建提交，1审核通过，2审核拒绝，3上线中，4上线完成，5上线失败
    status_new = 0
    status_pass = 1
    status_reject = 2
    status_doing = 3
    status_success = 4
    status_fail = 5

    status_memo = {
        status_new: '新建提交',
        status_pass: '审核通过',
        status_reject: '审核拒绝',
        status_doing: '上线中',
        status_success: '上线完成',
        status_fail: '上线失败',
    }
    rollback_count = {}
    keep_version_num = 3

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    name = db.Column(String(100))
    user_id = db.Column(Integer)
    user_name = db.Column(String(10))
    project_id = db.Column(Integer)
    action = db.Column(Integer)
    status = db.Column(Integer)
    link_id = db.Column(String(100))
    ex_link_id = db.Column(String(100))
    servers = db.Column(Text)
    commit_id = db.Column(String(40))
    branch = db.Column(String(100))
    tag = db.Column(String(100))
    file_transmission_mode = db.Column(Integer)
    file_list = db.Column(Text)
    is_rollback = db.Column(Integer)
    created_at = db.Column(DateTime, default=datetime.now)
    updated_at = db.Column(DateTime, default=datetime.now, onupdate=datetime.now)

    taskMdl = None

    def table_name(self):
        return self.__tablename__

    #
    # def list(self, page=0, size=10, kw=''):
    #     data = Task.query.order_by('id').offset(int(size) * int(page)).limit(size).all()
    #     return [p.to_json() for p in data]
    #
    # def one(self):
    #     project_info = Project.query.filter_by(id=self.taskMdl.get('project_id')).one().to_json()
    #     return dict(project_info, **self.taskMdl)
    #

    def list(self, page=0, size=10, space_id=None, kw=None):
        """
        获取分页列表
        :param page:
        :param size:
        :param kw:
        :return:
        """
        self.rollback_count.clear()
        query = TaskModel.query.filter(TaskModel.status.notin_([self.status_remove]))
        if kw:
            query = query.filter(TaskModel.name.like('%' + kw + '%'))

        # 关联 projects
        ProjectModel = model.project.ProjectModel
        query = query.join(ProjectModel, TaskModel.project_id == ProjectModel.id)
        query = query.filter(ProjectModel.status.notin_([self.status_remove]))

        # 关联 environments
        EnvironmentModel = model.environment.EnvironmentModel
        query = query.join(EnvironmentModel, EnvironmentModel.id == ProjectModel.environment_id)
        query = query.filter(EnvironmentModel.status.notin_([self.status_remove]))

        if space_id:
            query = query.filter(ProjectModel.space_id == space_id)

        query = query.add_columns(ProjectModel.name, EnvironmentModel.name, ProjectModel.keep_version_num)
        count = query.count()

        data = query.order_by(TaskModel.id.desc()) \
            .offset(int(size) * int(page)).limit(size) \
            .all()
        task_list = []
        for p in data:
            p[0].keep_version_num = p[3]
            item = p[0].to_json()
            item['project_name'] = p[1]
            item['environment_name'] = p[2]
            # self.keep_version_num = p[3]
            task_list.append(item)

        return task_list, count

    def item(self, id=None):
        """
        获取单条记录
        :param role_id:
        :return:
        """
        id = id if id else self.id
        data = self.query.filter(TaskModel.status.notin_([self.status_remove])).filter_by(id=id).first()
        if not data:
            return []

        task = data.to_json()
        ProjectModel = model.project.ProjectModel
        project = ProjectModel().item(task['project_id'])
        task['project_name'] = project['name'] if project else '未知项目'
        task['project_info'] = project
        return task

    def add(self, *args, **kwargs):
        data = dict(*args)
        project = TaskModel(**data)

        db.session.add(project)
        db.session.commit()

        if project.id:
            self.id = project.id

        return project.to_json()

    def update(self, *args, **kwargs):
        update_data = dict(*args)
        return super(TaskModel, self).update(**update_data)

    def remove(self, id=None):
        """

        :param role_id:
        :return:
        """
        id = id if id else self.id
        self.query.filter_by(id=id).update({'status': self.status_remove})
        ret = db.session.commit()

        return ret

    def to_json(self):
        ServerModel = model.server.ServerModel

        item = {
            'id': self.id,
            'name': self.name,
            'user_id': int(self.user_id),
            'user_name': self.user_name,
            'project_id': int(self.project_id),
            'project_name': self.project_id if self.project_id else '',
            'action': self.action,
            'status': self.status,
            'link_id': self.link_id,
            'ex_link_id': self.ex_link_id,
            'servers': self.servers,
            'servers_info': ServerModel.fetch_by_id(self.servers.split(',')) if self.servers else '',
            'commit_id': self.commit_id,
            'branch': self.branch,
            'tag': self.tag,
            'file_transmission_mode': self.file_transmission_mode,
            'file_list': self.file_list,
            'is_rollback': self.is_rollback,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
        item.update(self.enable())
        return item

    def enable(self):
        is_project_master = self.project_id in session['project_master']

        if self.project_id not in self.rollback_count:
            self.rollback_count[self.project_id] = 0
        if self.status in [self.status_doing, self.status_fail, self.status_success]:
            self.rollback_count[self.project_id] += 1

        if self.rollback_count[self.project_id] <= self.keep_version_num \
            and self.status in [self.status_doing, self.status_fail, self.status_success] \
            and self.ex_link_id:
            enable_rollback = True
        else:
            enable_rollback = False

        return {
            'enable_view': True if self.status in [self.status_doing, self.status_fail, self.status_success] else False,
            'enable_update': (permission.enable_uid(self.user_id) or permission.role_upper_developer() or is_project_master) and (self.status in [self.status_new, self.status_reject]),
            'enable_delete': (permission.enable_uid(self.user_id) or permission.role_upper_developer() or is_project_master) and (self.status in [self.status_new, self.status_pass, self.status_reject]),
            'enable_create': False,
            'enable_online': (permission.enable_uid(self.user_id) or permission.role_upper_developer() or is_project_master) and (self.status in [self.status_pass, self.status_fail, self.status_doing]),
            'enable_audit': (permission.role_upper_developer() or is_project_master) and (self.status in [self.status_new]),
            'enable_rollback': enable_rollback
        }

    @classmethod
    def task_default_status(cls, project_id):
        ProjectModel = model.project.ProjectModel
        project_info = ProjectModel.query.filter_by(id=project_id).first()
        if project_info.task_audit == ProjectModel.task_audit_true:
            return TaskModel.status_new
        else:
            return TaskModel.status_pass
