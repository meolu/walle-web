# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-26 16:29:58
    :author: wushuiyong@walle-web.io
"""
from datetime import datetime

from flask import current_app
from sqlalchemy import String, Integer, DateTime
from walle import model
from walle.model.database import SurrogatePK
from walle.model.database import db, Model, or_
from walle.model.user import UserModel
from walle.service.rbac.role import *


# 项目配置表
class MemberModel(SurrogatePK, Model):
    __tablename__ = 'members'

    current_time = datetime.now
    group_id = None
    project_id = None

    source_type_project = 'project'
    source_type_group = 'group'

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    user_id = db.Column(Integer, db.ForeignKey('users.id'))
    source_id = db.Column(Integer)
    source_type = db.Column(String(10))
    access_level = db.Column(String(10))
    status = db.Column(Integer)
    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)
    group_name = None

    def spaces(self, user_id=None):
        """
        获取分页列表
        :param page:
        :param size:
        :return:
        """
        SpaceModel = model.space.SpaceModel
        filters = {
            MemberModel.status.notin_([self.status_remove]),
            MemberModel.source_type == self.source_type_group,
            SpaceModel.status.notin_([self.status_remove]),
        }
        query = self.query.filter(*filters).with_labels()\
            .with_entities(MemberModel.source_id, MemberModel.access_level, SpaceModel.name)
        if user_id:
            query = query.filter_by(user_id=user_id)

        query = query.join(SpaceModel, SpaceModel.id == MemberModel.source_id)

        spaces = query.all()
        current_app.logger.info(spaces)
        return {space[0]: {'id': space[0], 'role': space[1], 'name': space[2]} for space in spaces}

    def projects(self, user_id=None, space_id=None):
        """
        获取分页列表
        :param page:
        :param size:
        :return:
        """
        filters = {
            MemberModel.status.notin_([self.status_remove]),
            MemberModel.source_type == self.source_type_project
        }
        query = self.query.filter(*filters)
        if user_id:
            query = query.filter_by(user_id=user_id)

        projects = query.all()
        current_app.logger.info(projects)

        return projects

    def project_master(self):
        filters = {
            MemberModel.status.notin_([self.status_remove]),
            MemberModel.source_type == self.source_type_project,
            MemberModel.user_id == current_user.id,
            MemberModel.access_level == MASTER,
        }
        query = self.query.filter(*filters)
        projects = query.with_entities(MemberModel.source_id).all()
        return [project[0] for project in projects]

    def update_group(self, members, group_name=None):
        SpaceModel = model.space.SpaceModel

        # 修复空间名称
        if group_name:
            SpaceModel(id=self.group_id).update({'name': group_name})
        # # 修改tag信息
        # if group_name:
        #     tag_model = TagModel.query.filter_by(label='user_group').filter_by(id=self.group_id).first()
        #     if tag_model.name != group_name:
        #         tag_model.name = group_name

        # 修改用户组成员
        # clean up
        filters = {
            MemberModel.source_id == self.group_id,
            MemberModel.source_type == self.source_type_group,
        }
        MemberModel.query.filter(*filters).delete()

        current_app.logger.info(members)
        # insert all
        for member in members:
            current_app.logger.info(member)
            current_app.logger.info(member['role'])
            update = {
                'user_id': member['user_id'],
                'source_id': self.group_id,
                'source_type': self.source_type_group,
                'access_level': member['role'].upper(),
                'status': self.status_available,
            }
            m = MemberModel(**update)
            db.session.add(m)

        ret = db.session.commit()

        return ret

    def update_project(self, project_id, members, group_name=None):
        space_info = model.project.ProjectModel.query.filter_by(id=project_id).first().to_json()
        space_members, count, user_ids = self.members(group_id=space_info['space_id'], size=-1)
        update_uids = []

        for member in members:
            update_uids.append(member['user_id'])

        current_app.logger.info(user_ids)
        current_app.logger.info(update_uids)

        # project新增用户是否在space's group中,无则抛出
        if list(set(update_uids).difference(set(user_ids))):
            raise WalleError(Code.user_not_in_space)

        # 修改用户组成员
        # clean up
        filters = {
            MemberModel.source_id == project_id,
            MemberModel.source_type == self.source_type_project,
        }
        MemberModel.query.filter(*filters).delete()

        # insert all
        for member in members:
            insert = {
                'user_id': member['user_id'],
                'source_id': project_id,
                'source_type': self.source_type_project,
                'access_level': member['role'].upper(),
                'status': self.status_available,
            }
            group = MemberModel(**insert)
            db.session.add(group)

        ret = db.session.commit()

        return ret

    def members(self, group_id=None, project_id=None, page=0, size=10, kw=None):
        """
        获取单条记录
        :param role_id:
        :return:
        """
        group_id = group_id if group_id else self.group_id
        project_id = project_id if project_id else self.project_id
        source_id = group_id if group_id else project_id
        source_type = self.source_type_group if group_id else self.source_type_project
        query = UserModel.query \
            .filter(UserModel.status.notin_([self.status_remove])) \
            .filter(MemberModel.source_id == source_id) \
            .filter(MemberModel.source_type == source_type)
        query = query.join(MemberModel, UserModel.id == MemberModel.user_id)
        if kw:
            query = query.filter(or_(UserModel.username.like('%' + kw + '%'), UserModel.email.like('%' + kw + '%')))

        query = query.add_columns(MemberModel.access_level, UserModel.id)

        count = query.count()
        query = query.order_by(MemberModel.id.asc())
        if size > 0:
            query = query.offset(int(size) * int(page)).limit(size)
        data = query.all()

        current_app.logger.info(data)
        list = []
        user_ids = []
        for p in data:
            item = p[0].to_json()
            item['role'] = p[1]
            user_ids.append(p[2])
            list.append(item)
        current_app.logger.info(list)

        return list, count, user_ids

    def member(self, user_id, role, group_id=None, project_id=None):
        query = self.query
        if group_id:
            query = query.filter_by(source_id=group_id).filter_by(source_type=self.source_type_group)
        elif project_id:
            query = query.filter_by(project_id=project_id).filter_by(source_type=self.source_type_project)
        if user_id:
            query = query.filter_by(user_id=user_id)

        if query.count():
            query.update({'access_level': role})
        else:
            source_type = self.source_type_project if project_id else self.source_type_group
            source_id = project_id if project_id else group_id

            insert = {
                'user_id': user_id,
                'source_id': source_id,
                'source_type': source_type,
                'access_level': role.upper(),
                'status': self.status_available,
            }
            current_app.logger.info(insert)
            group = MemberModel(**insert)
            db.session.add(group)

        db.session.commit()

        return self.members(group_id=group_id)

    def remove(self, group_id=None, user_id=None, project_id=None):
        """

        :param role_id:
        :return:
        """
        if group_id:
            MemberModel.query.filter_by(group_id=group_id).update({'status': self.status_remove})
        elif user_id:
            MemberModel.query.filter_by(user_id=user_id).update({'status': self.status_remove})
        elif self.group_id:
            MemberModel.query.filter_by(group_id=self.group_id).update({'status': self.status_remove})
        elif project_id:
            MemberModel.query.filter_by(project_id=project_id).update({'status': self.status_remove})

        ret = db.session.commit()

        return ret

    def to_json(self):
        return {
            'id': self.id,
            'user_id': self.user_id,
            'group_id': self.group_id,
            'group_name': self.group_name,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
