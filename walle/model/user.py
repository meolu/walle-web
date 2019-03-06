#!/usr/bin/env python
# -*- coding: utf-8 -*-
# @Author: wushuiyong
# @Created Time : 日  1/ 1 23:43:12 2017
# @Description:

from datetime import datetime

from flask_login import UserMixin
from sqlalchemy import String, Integer, DateTime, or_
from walle import model
from walle.model.database import SurrogatePK, db, Model
from walle.service.extensions import permission
from walle.service.rbac.role import *
from werkzeug.security import check_password_hash, generate_password_hash
from flask import current_app
from flask_login import AnonymousUserMixin

class AnonymousUser(AnonymousUserMixin):
    @property
    def role(self):
        return None

class UserModel(UserMixin, SurrogatePK, Model):
    # 表的名字:
    __tablename__ = 'users'
    status_active = 1
    status_blocked = 2

    current_time = datetime.now
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
        update_data = dict(*args)
        return super(UserModel, self).update(**update_data)

    def update_avatar(self, avatar):
        d = {'avatar': avatar}
        user = self.query.get(self.id).update(**d)
        current_app.logger.info(user)

    def update_name_pwd(self, username, password=None):
        user = self.query.filter_by(id=self.id).first()
        if username:
            user.username = username
        if password:
            user.password = self.get_password(password)

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

    def get_password(self, password):
        """Set password."""
        return generate_password_hash(password)

    def general_password(self, password):
        """
        检查密码是否正确
        :param password:
        :return:
        """
        self.password = generate_password_hash(password)
        return generate_password_hash(password)

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
        MemberModel = model.member.MemberModel
        return MemberModel().spaces(user_id=self.id)

    def space_id(self):
        return session['space_id']

    @classmethod
    def fresh_session(cls):
        session['project_master'] = []
        # 0.超管
        if current_user.role == SUPER:
            return True

        spaces = current_user.has_spaces()

        # 1.无空间权限且非超管
        if not spaces and current_user.role != SUPER:
            raise WalleError(Code.space_empty)

        default_space = list(spaces.keys())[0]

        # 2.第一次登录无空间
        if not current_user.last_space:
            current_user.last_space = default_space
            current_user.save()
            session['space_id'] = default_space
            session['space_info'] = spaces[session['space_id']]

        # 3.空间权限有修改（上次登录的空格没有权限了）
        if current_user.last_space not in list(spaces.keys()):
            current_user.last_space = default_space


        # 4.项目管理员
        MemberModel = model.member.MemberModel()
        session['project_master'] = MemberModel.project_master()

        session['space_id'] = current_user.last_space
        session['space_info'] = spaces[current_user.last_space]
        session['space_list'] = list(spaces.values())

    @classmethod
    def avatar_url(cls, avatar):
        avatar = avatar if avatar else 'default.jpg'
        return current_app.config['AVATAR_PATH'] + avatar

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
            'enable_view': True,
            'enable_update': permission.role_upper_master(),
            'enable_delete': permission.role_upper_master(),
            'enable_create': False,
            'enable_online': False,
            'enable_audit': False,
            'enable_block': False,
        }
