#!/usr/bin/env python
# -*- coding: utf-8 -*-
# @Author: wushuiyong
# @Created Time : 日  1/ 1 23:43:12 2017
# @Description:

from sqlalchemy import String, Integer, DateTime

# from flask_cache import Cache
from datetime import datetime

from walle.model.database import SurrogatePK, db, Model

class TagModel(SurrogatePK, Model):
    # 表的名字:
    __tablename__ = 'tags'

    current_time = datetime.now

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
        return db.session.commit()

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
