#!/usr/bin/env python
# -*- coding: utf-8 -*-
# @Author: wushuiyong
# @Created Time : 日  1/ 1 23:43:12 2017
# @Description:

from flask_login import UserMixin
from sqlalchemy import String, Integer, DateTime, or_
from werkzeug.security import check_password_hash, generate_password_hash

# from flask_cache import Cache
from datetime import datetime
from walle.service.extensions import login_manager
from walle.model.database import SurrogatePK, db, Model
from walle.model.tag import TagModel
from sqlalchemy.orm import aliased
from walle.service.rbac.access import Access as AccessRbac
from flask import current_app, session, abort
from walle.service.rbac.role import *
from walle.service.error import WalleError
from flask_login import current_user as g
from walle.service.extensions import permission

import walle.model

class UserModel(UserMixin, SurrogatePK, Model):
    # 表的名字:
    __tablename__ = 'users'
    status_active = 1
    status_blocked = 2

    current_time = datetime.now()
    password_hash = 'sadfsfkk'
    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    username = db.Column(String(50))
    is_email_verified = db.Column(Integer, default=0)
    email = db.Column(String(50), unique=True, nullable=False)
    password = db.Column(String(50), nullable=False)
    avatar = db.Column(String(100))
    role = db.Column(String(10))
    status = db.Column(Integer, default=1)
    last_space = db.Column(Integer, default=0)
    # role_info = relationship("walle.model.user.RoleModel", back_populates="users")
    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

    status_mapping = {
        -1: '删除',
        0: '新建',
        1: '正常',
        2: '冻结',
    }

    '''
    current_user 基础方法
      "__abstract__",
      "__class__",
      "__delattr__",
      "__dict__",
      "__doc__",
      "__eq__",
      "__format__",
      "__getattribute__",
      "__hash__",
      "__init__",
      "__mapper__",
      "__module__",
      "__ne__",
      "__new__",
      "__reduce__",
      "__reduce_ex__",
      "__repr__",
      "__setattr__",
      "__sizeof__",
      "__str__",
      "__subclasshook__",
      "__table__",
      "__table_args__",
      "__tablename__",
      "__weakref__",
      "_cached_tablename",
      "_decl_class_registry",
      "_sa_class_manager",
      "_sa_instance_state",
      "avatar",
      "avatar_url",
      "block_active",
      "column_name_set",
      "create",
      "create_from_dict",
      "create_or_update",
      "created_at",
      "current_time",
      "delete",
      "dump_schema",
      "email",
      "enable",
      "fetch_access_list_by_role_id",
      "fetch_by_uid",
      "general_password",
      "get_by_id",
      "get_common_fields",
      "get_id",
      "id",
      "is_active",
      "is_anonymous",
      "is_authenticated",
      "is_email_verified",
      "item",
      "list",
      "metadata",
      "password",
      "password_hash",
      "query",
      "query_class",
      "query_paginate",
      "query_paginate_and_dump_schema",
      "remove",
      "save",
      "set_password",
      "status",
      "status_active",
      "status_available",
      "status_blocked",
      "status_default",
      "status_mapping",
      "status_remove",
      "to_dict",
      "to_json",
      "uid2name",
      "update",
      "update_avatar",
      "update_name_pwd",
      "updated_at",
      "username",
      "verify_password"
    '''
    def add(self, *args, **kwargs):
        data = dict(*args)
        user = UserModel(**data)

        db.session.add(user)
        db.session.commit()
        return user

    def item(self, user_id=None):
        """
        获取单条记录
        :param role_id:
        :return:
        """
        data = self.query.filter_by(id=self.id).filter(UserModel.status.notin_([self.status_remove])).first()
        return data.to_json() if data else []


    def update(self, *args, **kwargs):
        # todo permission_ids need to be formated and checked
        # a new type to update a model

        update_data = dict(*args)
        return super(UserModel, self).update(**update_data)

    def update_avatar(self, avatar):
        d = {'avatar': avatar}
        user = self.query.get(self.id).update(**d)
        current_app.logger.info(user)

    def update_name_pwd(self, username, password=None):
        # todo permission_ids need to be formated and checked
        user = self.query.filter_by(id=self.id).first()
        user.username = username
        if password:
            self.set_password(password)

        db.session.commit()
        return user.to_json()

    def block_active(self, status):
        user = self.query.filter_by(id=self.id).first()
        user.status = status
        db.session.commit()
        return user.to_json()

    def remove(self):
        """

        :param role_id:
        :return:
        """
        self.query.filter_by(id=self.id).update({'status': self.status_remove})

        ret = db.session.commit()

        return ret

    def verify_password(self, password):
        """
        检查密码是否正确
        :param password:
        :return:
        """
        if self.password is None:
            return False
        return check_password_hash(self.password, password)

    def set_password(self, password):
        """Set password."""
        self.password = generate_password_hash(password)

    def general_password(self, password):
        """
        检查密码是否正确
        :param password:
        :return:
        """
        self.password = generate_password_hash(password)
        return generate_password_hash(password)

    def fetch_access_list_by_role_id(self, role_id):
        module = aliased(MenuModel)
        controller = aliased(MenuModel)
        action = aliased(MenuModel)
        role = RoleModel.query.get(role_id)
        access_ids = role.access_ids.split(',')

        data = db.session \
            .query(controller.name_en, controller.name_cn,
                   action.name_en, action.name_cn) \
            .outerjoin(action, action.pid == controller.id) \
            .filter(module.type == MenuModel.type_module) \
            .filter(controller.id.in_(access_ids)) \
            .filter(action.id.in_(access_ids)) \
            .all()

        return [AccessRbac.resource(a_en, c_en) for c_en, c_cn, a_en, a_cn in data if c_en and a_en]

    def is_authenticated(self):
        return True

    def is_active(self):
        return True

    def is_anonymous(self):
        return False

    def get_id(self):
        try:
            return unicode(self.id)  # python 2
        except NameError:
            return str(self.id)  # python 3

    def list(self, uids=[], page=0, size=10, space_id=None, kw=None):
        """
        获取分页列表
        :param page:
        :param size:
        :return:
        """
        query = UserModel.query.filter(UserModel.status.notin_([self.status_remove]))
        if kw:
            query = query.filter(or_(UserModel.username.like('%' + kw + '%'), UserModel.email.like('%' + kw + '%')))
        if uids:
            query = query.filter(UserModel.id.in_(uids))

        count = query.count()
        data = query.order_by(UserModel.id.desc()).offset(int(size) * int(page)).limit(size).all()
        user_list = [p.to_json() for p in data]
        return user_list, count

    def has_spaces(self):
        return MemberModel().spaces(user_id=self.id)

    @classmethod
    def fresh_session(cls):
        # 0.超管
        if current_user.role == SUPER:
            return True

        spaces = current_user.has_spaces()

        # 1.无空间权限且非超管
        if not spaces and current_user.role <> SUPER:
            raise WalleError(Code.space_empty)

        default_space = spaces.keys()[0]

        # 2.第一次登录无空间
        if not current_user.last_space:
            current_user.last_space = default_space
            current_user.save()
            session['space_id'] = default_space
            session['space_info'] = spaces[session['space_id']]

        # 3.空间权限有修改
        if current_user.last_space and current_user.last_space not in spaces.keys():
            raise WalleError(Code.space_error)

        session['space_id'] = current_user.last_space
        session['space_info'] = spaces[current_user.last_space]
        session['space_list'] = spaces.values()

        current_app.logger.info('============ SecurityResource.__init__ ============')


    @classmethod
    def avatar_url(cls, avatar):
        avatar = avatar if avatar else 'default.jpg'
        return '/' + current_app.config['AVATAR_PATH'] + avatar

    @classmethod
    def fetch_by_uid(cls, uids=None):
        """
        用户列表
        :param uids: []
        :return:
        """
        if not uids:
            return []

        query = UserModel.query.filter(UserModel.id.in_(uids)).filter(UserModel.status.notin_([cls.status_remove]))
        data = query.order_by(UserModel.id.desc()).all()
        return [p.to_json() for p in data]

    @classmethod
    def uid2name(cls, data):
        """
        把uid转换成名字
        :param data: [{'user_id':1, 'xx':'yy'}] 至少包含user_id
        :return:
        """
        user_ids = []
        uid2name = {}
        for items in data:
            user_ids.append(items.user_id)
        user_info = cls.fetch_by_uid(uids=user_ids)

        for user in user_info:
            uid2name[user['id']] = user['username']
        return uid2name

    def to_json(self):
        item = {
            'id': int(self.id),
            'user_id': int(self.id),
            'username': self.username,
            'is_email_verified': self.is_email_verified,
            'email': self.email,
            'avatar': self.avatar_url(self.avatar),
            # TODO 当前登录用户的空间
            # 'role_id': self.role_id,
            'status': self.status_mapping[self.status],
            'last_space': self.last_space,
            # 'status': self.status,
            # 'role_name': self.role_id,
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

class MenuModel(SurrogatePK, Model):
    __tablename__ = 'menus'

    type_module = 'module'
    type_controller = 'controller'
    type_action = 'action'

    status_open = 1
    status_close = 2
    current_time = datetime.now()

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    name_cn = db.Column(String(30))
    name_en = db.Column(String(30))
    pid = db.Column(Integer)
    type = db.Column(String(30))
    sequence = db.Column(Integer)
    archive = db.Column(Integer)
    icon = db.Column(String(30))
    url = db.Column(String(30))
    visible = db.Column(Integer)
    role = db.Column(Integer)
    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

    def menu(self, role):
        data = {}
        filters = {
            MenuModel.visible == 1,
            MenuModel.role >= role
        }
        query = self.query \
            .filter(*filters) \
            .order_by('sequence asc') \
            .all()
        for item in query:
            if item.type == self.type_module:
                module = {
                    'title': item.name_cn,
                    'icon': item.icon,
                    'sub_menu': [],
                }
                if item.url:
                    module['url'] = RoleModel.menu_url(item.url)
                data[item.id] = module
            elif item.type == self.type_controller:
                data[item.pid]['sub_menu'].append({
                    'title': item.name_cn,
                    'icon': item.icon,
                    'url': RoleModel.menu_url(item.url),
                })

        return data.values()

    def list(self):
        """
        获取分页列表
        :param page:
        :param size:
        :param kw:
        :return:
        """
        menus_module = {}
        menus_controller = {}
        module = aliased(MenuModel)
        controller = aliased(MenuModel)
        action = aliased(MenuModel)

        data = db.session.query(module.id, module.name_cn, controller.id, controller.name_cn, action.id, action.name_cn) \
            .outerjoin(controller, controller.pid == module.id) \
            .outerjoin(action, action.pid == controller.id) \
            .filter(module.type == self.type_module) \
            .all()
        for m_id, m_name, c_id, c_name, a_id, a_name in data:
            # module
            if not menus_module.has_key(m_id):
                menus_module[m_id] = {
                    'id': m_id,
                    'title': m_name,
                    'sub_menu': {},
                }
            # controller
            if not menus_module[m_id]['sub_menu'].has_key(c_id) and c_name:
                menus_module[m_id]['sub_menu'][c_id] = {
                    'id': c_id,
                    'title': c_name,
                    'sub_menu': {},
                }
            # action
            if not menus_controller.has_key(c_id):
                menus_controller[c_id] = []
            if a_name:
                menus_controller[c_id].append({
                    'id': a_id,
                    'title': a_name,
                })
        menus = []
        for m_id, m_info in menus_module.items():
            for c_id, c_info in m_info['sub_menu'].items():
                m_info['sub_menu'][c_id]['sub_menu'] = menus_controller[c_id]
            menus.append({
                'id': m_id,
                'title': m_info['title'],
                'sub_menu': m_info['sub_menu'].values(),
            })

        return menus

    def to_json(self):
        return {
            'id': self.id,
            'name_cn': self.name_cn,
            'name_en': self.name_en,
            'pid': self.pid,
            'type': self.type,
            'sequence': self.sequence,
            'archive': self.archive,
            'icon': self.icon,
            'url': self.url,
            'visible': self.visible,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }


class RoleModel(object):
    _role_super = 'SUPER'

    _role_owner = 'OWNER'

    _role_master = 'MASTER'

    _role_developer = 'DEVELOPER'

    _role_reporter = 'REPORTER'

    @classmethod
    def list(cls):
        roles = [
            {'id': cls._role_super, 'name': '超级管理员'},
            {'id': cls._role_owner, 'name': '空间所有者'},
            {'id': cls._role_master, 'name': '项目管理员'},
            {'id': cls._role_developer, 'name': '开发者'},
            {'id': cls._role_reporter, 'name': '访客'},
        ]
        return roles, len(roles)

    @classmethod
    def item(cls, role_id):
        return None

    @classmethod
    def menu_url(cls, url):
        if url == '/':
            return url
        prefix = 'admin' if g.role == SUPER else session['space_info']['name']

        return '/' + prefix + url


# 项目配置表
class MemberModel(SurrogatePK, Model):
    __tablename__ = 'members'

    current_time = datetime.now()
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

    # TODO group id全局化

    def spaces(self, user_id=None):
        """
        获取分页列表
        :param page:
        :param size:
        :return:
        """
        filters = {
            MemberModel.status.notin_([self.status_remove]),
            MemberModel.source_type == self.source_type_group
        }
        query = self.query.filter(*filters).with_labels().with_entities(MemberModel.source_id, MemberModel.access_level, SpaceModel.name)
        if user_id:
            query = query.filter_by(user_id=user_id)

        query = query.join(SpaceModel, SpaceModel.id==MemberModel.source_id)

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

        # if project_id:
        #     query = query.filter_by(source_id=project_id)

        projects = query.all()
        current_app.logger.info(projects)

        return projects

        group, count = MemberModel.query_paginate(page=page, limit=size, filter_name_dict=filters)

        list = [p.to_json() for p in group]
        return list, count

    def add(self, space_name, members):
        """

        :param space_name:
        :param members: [{'user_id': 1, 'project_id': 2}]
        :return:
        """
        tag = TagModel(name=space_name, label='user_group')
        db.session.add(tag)
        db.session.commit()


        for member in members:
            user_group = MemberModel(group_id=tag.id, user_id=member['user_id'], project_id=member['project_id'])
            db.session.add(user_group)

        db.session.commit()

        if tag.id:
            self.group_id = tag.id

        return tag.id

    def update_group(self, members, group_name=None):
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

        # insert all
        for member in members:
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
        space_info = walle.model.project.ProjectModel.query.filter_by(id=project_id).first().to_json()
        group_model = self.members(group_id=space_info['space_id'])
        user_update = []

        for member in members:
            user_update.append(member['user_id'])

        current_app.logger.info(group_model['user_ids'])
        current_app.logger.info(user_update)

        # project新增用户是否在space's group中,无则抛出
        if list(set(user_update).difference(set(group_model['user_ids']))):
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

    def members(self, group_id=None, project_id=None, page=1, size=10):
        """
        获取单条记录
        :param role_id:
        :return:
        """
        group_id = group_id if group_id else self.group_id
        project_id = project_id if project_id else self.project_id
        source_id = group_id if group_id else project_id
        source_type = self.source_type_group if group_id else self.source_type_project
        filters = {
            'status': {'nin': [self.status_remove]},
            'source_id': {'=': source_id},
            'source_type': {'=': source_type},
        }

        # TODO
        groups, count = MemberModel.query_paginate(page=page, limit=size, filter_name_dict=filters)

        user_ids = []
        user_role = members = {}
        current_app.logger.info(groups)

        for group_info in groups:
            user_ids.append(group_info.user_id)
            # TODO
            user_role[group_info.user_id] = group_info.access_level

        current_app.logger.info(user_ids)
        user_model = UserModel()
        user_info = user_model.fetch_by_uid(uids=set(user_ids))
        if user_info:
            for user in user_info:
                if user_role.has_key(user['id']):
                    user['role'] = user_role[user['id']]

        members['user_ids'] = user_ids
        members['members'] = user_info
        members['users'] = len(user_ids)
        return members

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


# 项目配置表
class SpaceModel(SurrogatePK, Model):
    # 表的名字:
    __tablename__ = 'spaces'
    current_time = datetime.now()
    status_close = 0
    status_open = 1

    # 表的结构:
    id = db.Column(Integer, primary_key=True, autoincrement=True)
    user_id = db.Column(Integer)
    name = db.Column(String(100))
    status = db.Column(Integer)

    created_at = db.Column(DateTime, default=current_time)
    updated_at = db.Column(DateTime, default=current_time, onupdate=current_time)

    def list(self, page=0, size=10, kw=None):
        """
        获取分页列表
        :param page:
        :param size:
        :return:
        """
        query = self.query.filter(SpaceModel.status.notin_([self.status_remove]))
        if kw:
            query = query.filter(SpaceModel.name.like('%' + kw + '%'))

        # TODO 如果是超管,可以全量,否则需要过滤自己有权限的空间列表
        if g.role <> SUPER:
            query = query.filter_by(user_id=g.id)
        count = query.count()
        data = query.order_by(SpaceModel.id.desc()).offset(int(size) * int(page)).limit(size).all()

        uid2name = UserModel.uid2name(data=data)
        list = [p.to_json(uid2name) for p in data]
        return list, count

    def item(self, id=None):
        """
        获取单条记录
        :param role_id:
        :return:
        """
        id = id if id else self.id
        data = self.query.filter_by(id=id).first()
        members = MemberModel(group_id=id).members()

        if not data:
            return []

        data = data.to_json()

        return dict(data, **members)

    def add(self, *args, **kwargs):
        # todo permission_ids need to be formated and checked
        data = dict(*args)

        # tag = TagModel(name=data['name'], label='user_group')
        # db.session.add(tag)
        # db.session.commit()
        data = dict(*args)
        space = SpaceModel(**data)
        db.session.add(space)
        db.session.commit()


        self.id = space.id
        return self.id

    def update(self, *args, **kwargs):
        # todo permission_ids need to be formated and checked
        # a new type to update a model

        update_data = dict(*args)
        return super(SpaceModel, self).update(**update_data)

    def remove(self, space_id=None):
        """

        :param space_id:
        :return:
        """
        space_id = space_id if space_id else self.id
        SpaceModel.query.filter_by(id=space_id).update({'status': self.status_remove})

        ret = db.session.commit()

        return ret

    def to_json(self, uid2name=None):
        item = {
            'id': self.id,
            'user_id': self.user_id,
            'user_name': uid2name[self.user_id] if uid2name and uid2name.has_key(self.user_id) else '',
            # TODO
            'group_id': 'self.group_id',
            'name': self.name,
            'status': self.status,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
        item.update(self.enable())
        return item

    def enable(self):
        return {
            'enable_update': permission.enable_uid(self.user_id) or permission.enable_role(OWNER),
            'enable_delete': permission.enable_uid(self.user_id) or permission.enable_role(OWNER),
            'enable_create': False,
            'enable_online': False,
            'enable_audit': False,
            'enable_block': permission.enable_role(MASTER),
        }

