# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-11-24 06:30:06
    :author: wushuiyong@walle-web.io
"""
from datetime import datetime

from sqlalchemy import String, Integer, DateTime
from walle.model.database import db, Model


# 上线记录表
class RecordModel(Model):
    # 表的名字:
    __tablename__ = 'records'
    current_time = datetime.now
    #
    stage_end = 'end'
    #
    status_success = 0
    #
    status_fail = 1

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
        record = RecordModel(stage=stage, sequence=sequence, user_id=user_id,
                             task_id=task_id, status=status, host=host, user=user, command=command,
                             success=success, error=error)
        db.session.add(record)
        ret = db.session.commit()

        return ret

    def fetch(self, task_id):
        data = self.query.filter_by(task_id=task_id).order_by(RecordModel.id.asc()).all()
        return [p.to_json() for p in data]

    @classmethod
    def logs(cls, host, user, command, status, stage, sequence, success, error, *args, **kwargs):
        return {
            'host': host,
            'user': user,
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
