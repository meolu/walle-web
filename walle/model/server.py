# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-24 06:15:11
    :author: wushuiyong@walle-web.io
"""
from datetime import datetime

from sqlalchemy import String, Integer, DateTime
from walle.model.database import SurrogatePK, db, Model
from walle.service.extensions import permission
from walle.service.rbac.role import *


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

        data = query.order_by(ServerModel.id.desc()) \
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
        data = query.order_by(ServerModel.id.desc()).all()
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
        return {
            'enable_update': permission.role_upper_developer(),
            'enable_delete': permission.role_upper_developer(),
            'enable_create': False,
            'enable_online': False,
            'enable_audit': permission.role_upper_owner(),
            'enable_block': False,
        }
