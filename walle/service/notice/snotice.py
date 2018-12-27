# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2018-12-23 20:15:30
    :author: wushuiyong@walle-web.io
"""
# # import . as this
# from walle.service.error import WalleError, Code
#
#
# class Notice():
#     by_dingding = 'Dingding'
#
#     by_email = 'Eamil'
#
#     def deploy_task(self):
#         pass
#
#     @classmethod
#     def create(cls, by):
#         '''
#         usage:
#         create Dingding
#         Notice.create(Notice.by_dingding)
#
#         @param by:
#         @return:
#         '''
#         if by == cls.by_dingding:
#             return .dingding.Dingding()
#         elif by == cls.by_email:
#             pass
#             # return .email.Email()
#         else:
#             raise WalleError(Code.sys_params_err)
