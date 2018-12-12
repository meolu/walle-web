# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2018-11-11 19:49:37
    :author: wushuiyong@walle-web.io
"""


class Code():

    #: 没有消息就是好消息
    Ok = 0

    #: ----------------------- 1xxx 用户相关错误 -----------------
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

    #: 用户不存在
    user_not_in_space = 1005

    #: ----------------------- 2xxx 前端相关错误 -----------------
    #: 2xxx 前端相关错误
    #: 参数错误
    params_error = 2000

    #: 表单错误
    form_error = 2001

    #: ----------------------- 3xxx shell 相关错误 -----------------
    #: 3xxx shell相关错误
    #: 不知道怎么归类的错误
    shell_run_fail = 3000

    #: 目录不存在
    shell_dir_not_exists = 3001

    #: ----------------------- 4xxx git 相关错误 -----------------
    #: 3xxx shell相关错误
    #: git尚未初始化
    shell_git_init_fail = 4001

    #: git pull 失败
    shell_git_pull_fail = 4002

    code_msg = {
        unlogin: '未登录',
        error_pwd: '账号密码错误',
        not_allow: '无此资源权限',
        space_empty: '尚未开通空间，请联系空间负责人加入空间',
        space_error: '无此空间权限',
        user_not_in_space: '用户不存在此空间，请联系空间所有人添加此用户到用户组',

        params_error: '参数错误',
        form_error: '表单错误',

        shell_run_fail: '命令运行错误，请联系管理员',
        shell_dir_not_exists: '路径不存在，请联系管理员',

        shell_git_init_fail: '项目git初始化失败，请联系管理员',
        shell_git_pull_fail: 'git pull 失败，请联系管理员',
    }
