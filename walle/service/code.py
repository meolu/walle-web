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

    #: 无此权限
    not_allow = 1001

    #: 尚未开通空间
    space_empty = 1002

    #: 无此空间权限
    space_error = 1003


    #: 2xxx 表示参数错误
    params_error = 2000


    code_msg = {
        unlogin: '未登录',
        not_allow: '无此权限',
        params_error: '参数错误',
        space_empty: '尚未开通空间, 请联系空间负责人加入空间',
        space_error: '无此空间权限',
    }


