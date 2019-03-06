# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-26 16:30:44
    :author: wushuiyong@walle-web.io
"""

from datetime import datetime

from sqlalchemy import String, Integer, DateTime
from walle import model
from walle.model.database import SurrogatePK
from walle.model.database import db, Model


class MenuModel(SurrogatePK, Model):
    __tablename__ = 'menus'

    type_module = 'module'
    type_controller = 'controller'
    type_action = 'action'

    status_open = 1
    status_close = 2
    current_time = datetime.now

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
        RoleModel = model.role.RoleModel
        data = {}
        filters = {
            MenuModel.visible == 1,
            MenuModel.role <= role
        }
        query = self.query \
            .filter(*filters) \
            .order_by(MenuModel.sequence.asc()) \
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

        return list(data.values())

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
