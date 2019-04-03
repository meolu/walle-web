# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-25 16:01:41
    :author: wushuiyong@walle-web.io
"""
from datetime import datetime

from sqlalchemy import String, Integer, DateTime
from walle.model.database import SurrogatePK, db, Model
from walle.model.user import UserModel
from walle.service.extensions import permission
from walle.service.rbac.role import *
from walle import model

# 项目配置表
class SpaceModel(SurrogatePK, Model):
    # 表的名字:
    __tablename__ = 'spaces'
    current_time = datetime.now
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
        if current_user.role != SUPER:
            query = query.filter_by(user_id=current_user.id)
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
        MemberModel = model.member.MemberModel
        id = id if id else self.id
        data = self.query.filter_by(id=id).first()

        if not data:
            return []

        data = data.to_json()
        data['members'], count, user_ids = MemberModel(group_id=id).members()

        return data

    def add(self, *args, **kwargs):
        data = dict(*args)
        space = SpaceModel(**data)
        db.session.add(space)
        db.session.commit()

        self.id = space.id
        return self.id

    def update(self, *args, **kwargs):
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
            'user_name': uid2name[self.user_id] if uid2name and self.user_id in uid2name else '',
            'name': self.name,
            'status': self.status,
            'created_at': self.created_at.strftime('%Y-%m-%d %H:%M:%S'),
            'updated_at': self.updated_at.strftime('%Y-%m-%d %H:%M:%S'),
        }
        item.update(self.enable())
        return item

    def enable(self):
        return {
            'enable_view': True,
            'enable_update': permission.enable_uid(self.user_id) or permission.role_upper_owner(),
            'enable_delete': permission.enable_uid(self.user_id) or permission.role_upper_owner(),
            'enable_create': False,
            'enable_online': False,
            'enable_audit': False,
            'enable_block': permission.role_upper_master(),
        }
