# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2019 walle-web.io
    :created time: 2019-02-24 10:47:53
    :author: wushuiyong@walle-web.io
"""

import os
import re
import os.path as osp
import getpass
import git as PyGit
from git import Repo as PyRepo
from walle.model.record import RecordModel


class Repo:

    '''
    @todo
    需要把websocket接入
    '''
    path = None

    def __init__(self, path=None):
        self.path = path


    def log(self, wenv):
        '''

        @param wenv:

            @param stage:
            @param sequence:
            @param user_id:
            @param task_id:
            @param status:
            @param host:
            @param user:
            @param command:
            @param success:
            @param error:

        @return:
        '''
        RecordModel().save_record(stage=wenv['stage'], sequence=wenv['sequence'], user_id=wenv['user_id'],
                                  task_id=wenv['task_id'], status=exitcode, host='127.0.0.1', user=getpass.getuser(),
                                  command=result.command, success=stdout,
                                  error=stderr)

    def websocket(self):
        ws_dict = {
            'user': getpass.getuser(),
            'host': '127.0.0.1',
            'cmd': command,
            'status': exitcode,
            'stage': wenv['stage'],
            'sequence': wenv['sequence'],
            'success': stdout,
            'error': stderr,
        }
        if wenv['console']:
            emit('console', {'event': 'task:console', 'data': ws_dict}, room=wenv['task_id'])

        pass


    def is_git_dir(self):
        '''
        判断是否为git目录

        @param path:
        @return:
        '''
        d = self.path + '/.git'
        if osp.isdir(d):
            if osp.isdir(osp.join(d, 'objects')) and osp.isdir(osp.join(d, 'refs')):
                headref = osp.join(d, 'HEAD')
                return osp.isfile(headref) or \
                       (osp.islink(headref) and
                        os.readlink(headref).startswith('refs'))
            elif (osp.isfile(osp.join(d, 'gitdir')) and
                  osp.isfile(osp.join(d, 'commondir')) and
                  osp.isfile(osp.join(d, 'gitfile'))):
                return False
        return False

    def init(self, url):
        # 创建目录
        if not os.path.exists(self.path):
            os.makedirs(self.path)
        # git clone
        if self.is_git_dir():
            return self.pull()
        else:
            return self.clone(url)

    def clone(self, url):
        '''
        检出项目

        @param branch:
        @param kwargs:
        @return:
        '''
        return PyRepo.clone_from(url, self.path)

    def pull(self):
        '''
        更新项目

        @param branch:
        @param kwargs:
        @return:
        '''
        repo = PyRepo(self.path)

        return repo.remote().pull()

    def checkout_2_branch(self, branch):
        '''
        切换到某个分支

        @param branch:
        @return:
        '''
        PyRepo(self.path).git.checkout(branch)

    def checkout_2_commit(self, branch, commit):
        '''
        切换分支的某个commit

        @param branch:
        @param commit:
        @return:
        '''
        self.checkout_2_branch(branch=branch)
        PyRepo(self.path).git.reset('--hard', commit)

    def checkout_2_tag(self, tag):
        '''
        切换到tag

        @param tag:
        @return:
        '''
        PyRepo(self.path).git.checkout(tag)

    def branches(self):
        '''
        获取所有分支

        @param branch:
        @param kwargs:
        @return:
        '''
        # 去除 origin/HEAD -> 当前指向
        # 去除远端前缀
        branches = PyRepo(self.path).remote().refs
        # fixbug https://github.com/meolu/walle-web/issues/705
        return [str(branch).strip().lstrip('origin').lstrip('/') for branch in branches if
                not str(branch).strip().startswith('origin/HEAD')]

    def tags(self):
        '''
        获取所有tag，按时间倒序

        @param branch:
        @param kwargs:
        @return:
        '''
        return [str(tag) for tag in PyRepo(self.path).tags][-10:]

    def commits(self, branch):
        '''
        获取分支的commits

        @param branch:
        @param kwargs:
        @return:
        '''
        self.checkout_2_branch(branch)

        commit_log = PyGit.Git(self.path).log('--pretty=%h #@_@# %an #@_@# %s', max_count=50)
        commit_list = commit_log.split('\n')
        commits = []
        for commit in commit_list:
            if not re.search('^.+ #@_@# .+ #@_@# .*$', commit):
                continue

            commit_dict = commit.split(' #@_@# ')
            from flask import current_app
            current_app.logger.info(commit_dict)
            commits.append({
                'id': commit_dict[0],
                'name': commit_dict[1],
                'message': commit_dict[2],
            })

        return commits
