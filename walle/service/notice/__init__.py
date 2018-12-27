# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-12-23 20:15:27
    :author: wushuiyong@walle-web.io
"""
from flask import current_app
from walle.service.error import WalleError, Code



class Notice():
    by_dingding = 'dingding'

    by_email = 'email'

    def deploy_task(self, project_info, notice_info):
        pass

    @classmethod
    def task_url(cls, project_name, task_id):
        return '%s//%s/%s/task/deploy/%s' % ('https' if current_app.config.get('SSL') else 'http',
                                             current_app.config.get('HOST'),
                                             project_name, task_id)

    @classmethod
    def create(cls, by):
        '''
        usage:
        create Dingding
        Notice.create(Notice.by_dingding)

        @param by:
        @return:
        '''
        if by.lower() == cls.by_dingding:
            from walle.service.notice.dingding import Dingding
            return Dingding()
        elif by.lower() == cls.by_email:
            from walle.service.notice.email import Email
            return Email()
        else:
            return Notice()
