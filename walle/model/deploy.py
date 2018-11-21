#!/usr/bin/env python
# -*- coding: utf-8 -*-
# @Author: wushuiyong
# @Created Time : 日  1/ 1 23:43:12 2017
# @Description:

from sqlalchemy import String, Integer, Text, DateTime
# from flask_cache import Cache
from datetime import datetime

from walle.model.database import SurrogatePK, db, Model
from walle.model.user import UserModel
from walle.service.rbac.role import *
from walle.service.extensions import permission


# 上线单
class TaskModel(SurrogatePK, Model):
    __tablename__ = 'tasks'
    current_time = datetime.now()
    # 状态0：新建提交，1审核通过，2审核拒绝，3上线完成，4上线失败
    status_new = 0
    status_pass = 1
    status_reject = 2
    status_success = 3
    status_fail = 4

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    name = db.Column(String(100))
    user_id = db.Column(Integer)
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
    enable_rollback = db.Column(Integer)
    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

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

    def list(self, page=0, size=10, kw=None):
        """
        获取分页列表
        :param page:
        :param size:
        :param kw:
        :return:
        """
        query = TaskModel.query.filter(TaskModel.status.notin_([self.status_remove]))
        if kw:
            query = query.filter(TaskModel.name.like('%' + kw + '%'))
        count = query.count()

        data = query.order_by('id desc') \
            .offset(int(size) * int(page)).limit(size) \
            .all()
        task_list = []

        for task in data:
            task = task.to_json()
            project = ProjectModel().get_by_id(task['project_id']).to_dict()
            task['project_name'] = project['name'] if project else u'未知项目'
            task_list.append(task)

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
        project = ProjectModel().get_by_id(task['project_id']).to_dict()
        task['project_name'] = project['name'] if project else u'未知项目'
        task['project_info'] = project
        return task

    def add(self, *args, **kwargs):
        # todo permission_ids need to be formated and checked
        data = dict(*args)
        project = TaskModel(**data)

        db.session.add(project)
        db.session.commit()

        if project.id:
            self.id = project.id

        return project.id

    def update(self, *args, **kwargs):
        # todo permission_ids need to be formated and checked
        # a new type to update a model

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
        item = {
            'id': self.id,
            'name': self.name,
            'user_id': int(self.user_id),
            'project_id': int(self.project_id),
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
            'enable_rollback': self.enable_rollback,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
        item.update(self.enable())
        return item

    def enable(self):
        return {
            # 'enable_update': permission.enable_uid(self.user_id) or permission.enable_role(DEVELOPER),
            # 'enable_delete': permission.enable_uid(self.user_id) or permission.enable_role(DEVELOPER),
            'enable_create': False,
            # 'enable_online': permission.enable_uid(self.user_id) or permission.enable_role(DEVELOPER),
            # 'enable_audit': permission.enable_role(DEVELOPER),
            'enable_block': False,
        }


# 上线记录表
class TaskRecordModel(Model):
    # 表的名字:
    __tablename__ = 'task_records'
    current_time = datetime.now()

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    stage = db.Column(String(20))
    sequence = db.Column(Integer)
    user_id = db.Column(Integer)
    task_id = db.Column(Integer)
    status = db.Column(Integer)
    command = db.Column(String(200))
    host = db.Column(String(200))
    user = db.Column(String(200))
    success = db.Column(String(2000))
    error = db.Column(String(2000))
    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

    def save_record(self, stage, sequence, user_id, task_id, status, host, user, command, success=None, error=None):
        record = TaskRecordModel(stage=stage, sequence=sequence, user_id=user_id,
                                 task_id=task_id, status=status, host=host, user=user, command=command,
                                 success=success, error=error)
        db.session.add(record)
        ret = db.session.commit()

        return ret

    def fetch(self, task_id):
        data = self.query.filter_by(task_id=task_id).order_by('id desc').all()
        return [p.to_json() for p in data]

    @classmethod
    def logs(cls, host, command, status, stage, sequence, success, error, *args, **kwargs):
        return {
            'host': host,
            'cmd': command,
            'status': status,
            'stage': stage,
            'sequence': sequence,
            'success': success,
            'error': error,
        }

    def to_json(self):
        return {
            'id': self.id,
            'stage': self.stage,
            'sequence': self.sequence,
            'user_id': self.user_id,
            'task_id': self.task_id,
            'status': self.status,
            'host': self.host,
            'user': self.user,
            'command': self.command,
            'success': self.success,
            'error': self.error,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }


# 环境级别
class EnvironmentModel(Model):
    # 表的名字:
    __tablename__ = 'environments'

    status_open = 1
    status_close = 2
    current_time = datetime.now()

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    name = db.Column(String(20))
    status = db.Column(Integer)
    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

    def list(self, page=0, size=10, kw=None):
        """
        获取分页列表
        :param page:
        :param size:
        :param kw:
        :return:
        """
        query = self.query.filter(EnvironmentModel.status.notin_([self.status_remove]))
        if kw:
            query = query.filter(EnvironmentModel.name.like('%' + kw + '%'))
        count = query.count()

        data = query.order_by('id desc').offset(int(size) * int(page)).limit(size).all()
        env_list = [p.to_json() for p in data]
        return env_list, count

    def item(self, env_id=None):
        """
        获取单条记录
        :param role_id:
        :return:
        """
        data = self.query.filter(EnvironmentModel.status.notin_([self.status_remove])).filter_by(id=self.id).first()
        return data.to_json() if data else []

    def add(self, env_name):
        # todo permission_ids need to be formated and checked
        env = EnvironmentModel(name=env_name, status=self.status_open)

        db.session.add(env)
        db.session.commit()

        if env.id:
            self.id = env.id

        return env.id

    def update(self, env_name, status, env_id=None):
        # todo permission_ids need to be formated and checked
        role = EnvironmentModel.query.filter_by(id=self.id).first()
        role.name = env_name
        role.status = status
        ret = db.session.commit()

        return ret

    def remove(self, env_id=None):
        """

        :param role_id:
        :return:
        """
        self.query.filter_by(id=self.id).update({'status': self.status_remove})
        ret = db.session.commit()

        return ret

    def to_json(self):
        item = {
            'id': self.id,
            'status': self.status,
            'env_name': self.name,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
        item.update(self.enable())
        return item

    def enable(self):
        return {
            'enable_update': permission.enable_role(DEVELOPER),
            'enable_delete': permission.enable_role(DEVELOPER),
            'enable_create': False,
            'enable_online': False,
            'enable_audit': False,
            'enable_block': False,
        }


# server
class ServerModel(SurrogatePK, Model):
    __tablename__ = 'servers'

    current_time = datetime.now()

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    name = db.Column(String(100))
    host = db.Column(String(100))
    status = db.Column(Integer)
    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

    def list(self, page=0, size=10, kw=None):
        """
        获取分页列表
        :param page:
        :param size:
        :param kw:
        :return:
        """
        query = self.query.filter(ServerModel.status.notin_([self.status_remove]))
        if kw:
            query = query.filter(ServerModel.name.like('%' + kw + '%'))
        count = query.count()

        data = query.order_by('id desc') \
            .offset(int(size) * int(page)).limit(size) \
            .all()
        server_list = [p.to_json() for p in data]
        return server_list, count

    def item(self, id=None):
        """
        获取单条记录
        :param role_id:
        :return:
        """
        id = id if id else self.id
        data = self.query.filter(ServerModel.status.notin_([self.status_remove])).filter_by(id=id).first()
        return data.to_json() if data else []

    def add(self, name, host):
        # todo permission_ids need to be formated and checked
        server = ServerModel(name=name, host=host, status=self.status_available)

        db.session.add(server)
        db.session.commit()

        if server.id:
            self.id = server.id

        return server.id

    def update(self, name, host, id=None):
        # todo permission_ids need to be formated and checked
        id = id if id else self.id
        role = ServerModel.query.filter_by(id=id).first()

        if not role:
            return False

        role.name = name
        role.host = host

        ret = db.session.commit()

        return ret

    def remove(self, id=None):
        """

        :param role_id:
        :return:
        """
        id = id if id else self.id
        self.query.filter_by(id=id).update({'status': self.status_remove})

        ret = db.session.commit()

        return ret

    @classmethod
    def fetch_by_id(cls, ids=None):
        """
        用户列表
        :param uids: []
        :return:
        """
        if not ids:
            return None

        query = ServerModel.query.filter(ServerModel.id.in_(ids))
        data = query.order_by('id desc').all()
        return [p.to_json() for p in data]

    def to_json(self):
        item = {
            'id': self.id,
            'name': self.name,
            'host': self.host,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
        item.update(self.enable())
        return item

    def enable(self):
        # current_app.logger.info(dir(permission.app))
        # current_app.logger.info(permission.enable_uid(3))
        return {
            # 'enable_update': permission.enable_role(DEVELOPER),
            # 'enable_delete': permission.enable_role(DEVELOPER),
            'enable_create': False,
            'enable_online': False,
            # 'enable_audit': permission.enable_role(OWNER),
            'enable_block': False,
        }


# 项目配置表
class ProjectModel(SurrogatePK, Model):
    # 表的名字:
    __tablename__ = 'projects'
    current_time = datetime.now()
    status_close = 0
    status_open = 1

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
    target_user = db.Column(String(50))
    target_port = db.Column(String(20))
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
    enable_audit = db.Column(Integer)

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

        if environment_id:
            query = query.filter_by(environment_id=environment_id)
        if space_id:
            query = query.filter_by(space_id=space_id)
        count = query.count()
        data = query.order_by('id desc').offset(int(size) * int(page)).limit(size).all()
        list = [p.to_json() for p in data]
        return list, count

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

        data = data.to_json()

        server_ids = data['server_ids']
        data['servers_info'] = ServerModel.fetch_by_id(map(int, server_ids.split(',')))
        return data

    def add(self, *args, **kwargs):
        # todo permission_ids need to be formated and checked
        data = dict(*args)
        project = ProjectModel(**data)

        db.session.add(project)
        db.session.commit()

        self.id = project.id
        return self.id

    def update(self, *args, **kwargs):
        # todo permission_ids need to be formated and checked
        # a new type to update a model

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
            'target_user': self.target_user,
            'target_port': self.target_port,
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
            'enable_audit': self.enable_audit,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
        item.update(self.enable())
        return item

    def enable(self):
        current_app.logger.info(self.id)
        return {
            'enable_update': permission.is_gte_develop_or_uid(self.user_id),
            'enable_delete': permission.enable_uid(self.user_id) or permission.enable_role(DEVELOPER),
            'enable_create': False,
            'enable_online': False,
            'enable_audit': False,
            'enable_block': False,
        }


class TagModel(SurrogatePK, Model):
    # 表的名字:
    __tablename__ = 'tags'

    current_time = datetime.now()

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    name = db.Column(String(30))
    label = db.Column(String(30))
    label_id = db.Column(Integer, default=0)
    # users = db.relationship('Group', backref='group', lazy='dynamic')
    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

    def list(self):
        data = TagModel.query.filter(TagModel.status.notin_([self.status_remove])).filter_by(id=1).first()
        # # return data.tag.count('*').to_json()
        # # print(data)
        # return []
        return data.to_json() if data else []

    def remove(self, tag_id):
        """

        :param role_id:
        :return:
        """
        TagModel.query.filter_by(id=tag_id).update({'status': self.status_remove})

        ret = db.session.commit()

        return ret

    def to_json(self):
        # user_ids = []
        # for user in self.users.all():
        #     user_ids.append(user.user_id)
        return {
            'id': self.id,
            'group_id': self.id,
            'group_name': self.name,
            # 'users': user_ids,
            # 'user_ids': user_ids,
            'label': self.label,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
