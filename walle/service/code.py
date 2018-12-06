# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2018-11-11 19:49:37
    :author: wushuiyong@walle-web.io
"""


class Code():
    #: 1xxx 表示用户相关: 登录, 权限
    #: 未登录, 大概是永远不会变了
    unlogin = 1000

    #: 账号密码错误
    error_pwd = 1001

    #: 无此权限
    not_allow = 1002

    #: 尚未开通空间
    space_empty = 1003

    #: 无此空间权限
    space_error = 1004

    #: 2xxx 表示参数错误
    params_error = 2000

    #: 用户不存在
    user_not_in_space = 2001

    code_msg = {
        unlogin: '未登录',
        error_pwd: '账号密码错误',
        not_allow: '无此权限',
        params_error: '参数错误',
        space_empty: '尚未开通空间，请联系空间负责人加入空间',
        space_error: '无此空间权限',
        user_not_in_space: '用户不存在此空间，请联系空间所有人添加此用户到用户组',
    }
