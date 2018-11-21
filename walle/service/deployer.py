#!/usr/bin/env python
# -*- coding: utf-8 -*-
# @Author: wushuiyong
# @Created Time : 日  1/ 1 23:43:12 2017
# @Description:


import time
from datetime import datetime

import os
# from fabric import context_managers, colors
from flask import current_app

from walle.model import deploy as TaskModel
from walle.service.waller import Waller
from walle.model.deploy import ProjectModel
from flask_socketio import emit

# import fabric2.exceptions.GroupException

class DeploySocketIO:

    '''
    序列号
    '''
    stage = '0'

    sequence = 0
    stage_prev_deploy = 'prev_deploy'
    stage_deploy = 'deploy'
    stage_post_deploy = 'post_deploy'

    stage_prev_release = 'prev_release'
    stage_release = 'release'
    stage_post_release = 'post_release'

    task_id = '0'
    user_id = '0'
    taskMdl = None
    TaskRecord = None

    version = datetime.now().strftime('%Y%m%d%H%M%s')
    project_name = 'walden'
    dir_codebase = '/tmp/walle/codebase/'
    dir_codebase_project = dir_codebase + project_name

    # 定义远程机器
    # env.hosts = ['172.16.0.231', '172.16.0.177']

    dir_release = None
    dir_webroot = None

    connections, success, errors = {}, {}, {}
    release_version_tar, release_version = None, None
    local, websocket = None, None
    def __init__(self, task_id=None, project_id=None, websocket=None):
        self.local = Waller(host=current_app.config.get('LOCAL_SERVER_HOST'),
                            user=current_app.config.get('LOCAL_SERVER_USER'),
                            port=current_app.config.get('LOCAL_SERVER_PORT'))
        self.TaskRecord = TaskModel.TaskRecordModel()
        current_app.logger.info('DeploySocketIO.__init__ before')
        emit('message', {'msg': 'DeploySocketIO.__init__'})
        current_app.logger.info('DeploySocketIO.__init__ after')
        # if websocket:
        #     websocket.send_updates(__name__)
        #     self.websocket = websocket
        if task_id:
            self.task_id = task_id
            self.taskMdl = TaskModel.TaskModel().item(self.task_id)
            self.user_id = self.taskMdl.get('user_id')
            self.servers = self.taskMdl.get('servers_info')
            self.task = self.taskMdl.get('target_user')
            self.project_info = self.taskMdl.get('project_info')
        if project_id:
            self.project_id = project_id
            self.project_info = ProjectModel(id=project_id).item()

    def config(self):
        return {'task_id': self.task_id, 'user_id': self.user_id, 'stage': self.stage, 'sequence': self.sequence,
                'websocket': self.websocket}

    # ===================== fabric ================
    # SocketHandler
    def deploy(self):
        '''
        1.代码检出前要做的基础工作
        - 检查 当前用户
        - 检查 python 版本
        - 检查 git 版本
        - 检查 目录是否存在
        - 用户自定义命令

        :return:
        '''
        current_app.logger.info('deploy ing')
        emit('message', {'msg':  'deploy ing'})
        current_app.logger.info('deploy end')

        self.stage = self.stage_prev_deploy
        self.sequence = 1

        # TODO remove
        # result = self.local.run('sleep 30', wenv=self.config())

        # 检查 当前用户
        command = 'whoami'
        current_app.logger.info(command)
        emit('message', {'msg':  command})

        result = self.local.run(command, wenv=self.config())

        # 检查 python 版本
        command = 'python --version'
        result = self.local.run(command, wenv=self.config())
        current_app.logger.info(command)

        # 检查 git 版本
        command = 'git --version'
        result = self.local.run(command, wenv=self.config())
        current_app.logger.info(command)

        # 检查 目录是否存在
        command = 'mkdir -p %s' % (self.dir_codebase_project)
        # TODO remove
        current_app.logger.info(command)
        result = self.local.run(command, wenv=self.config())

        # 用户自定义命令
        command = self.project_info['prev_deploy']
        current_app.logger.info(command)
        with self.local.cd(self.dir_codebase_project):
            result = self.local.run(command, wenv=self.config())

            # SocketHandler.send_to_all({
            #     'type': 'user',
            #     'id': 33,
            #     'host': env.host_string,
            #     'command': command,
            #     'message': result.stdout,
            # })


class Deployer:

    '''
    序列号
    '''
    stage = '0'

    sequence = 0
    stage_prev_deploy = 'prev_deploy'
    stage_deploy = 'deploy'
    stage_post_deploy = 'post_deploy'

    stage_prev_release = 'prev_release'
    stage_release = 'release'
    stage_post_release = 'post_release'

    task_id = '0'
    user_id = '0'
    taskMdl = None
    TaskRecord = None

    version = datetime.now().strftime('%Y%m%d%H%M%s')
    project_name = 'walden'
    dir_codebase = '/tmp/walle/codebase/'
    dir_codebase_project = dir_codebase + project_name

    # 定义远程机器
    # env.hosts = ['172.16.0.231', '172.16.0.177']

    dir_release = None
    dir_webroot = None

    connections, success, errors = {}, {}, {}
    release_version_tar, release_version = None, None
    local, websocket = None, None

    def __init__(self, task_id=None, project_id=None, websocket=None):
        self.local = Waller(host=current_app.config.get('LOCAL_SERVER_HOST'),
                            user=current_app.config.get('LOCAL_SERVER_USER'),
                            port=current_app.config.get('LOCAL_SERVER_PORT'))
        self.TaskRecord = TaskModel.TaskRecordModel()
        if websocket:
            websocket.send_updates(__name__)
            self.websocket = websocket
        if task_id:
            self.task_id = task_id
            self.taskMdl = TaskModel.TaskModel().item(self.task_id)
            self.user_id = self.taskMdl.get('user_id')
            self.servers = self.taskMdl.get('servers_info')
            self.task = self.taskMdl.get('target_user')
            self.project_info = self.taskMdl.get('project_info')
        if project_id:
            self.project_id = project_id
            self.project_info = ProjectModel(id=project_id).item()

    def config(self):
        return {'task_id': self.task_id, 'user_id': self.user_id, 'stage': self.stage, 'sequence': self.sequence,
                'websocket': self.websocket}

    # ===================== fabric ================
    # SocketHandler
    def prev_deploy(self):
        '''
        1.代码检出前要做的基础工作
        - 检查 当前用户
        - 检查 python 版本
        - 检查 git 版本
        - 检查 目录是否存在
        - 用户自定义命令

        :return:
        '''
        self.stage = self.stage_prev_deploy
        self.sequence = 1

        # TODO remove
        # result = self.local.run('sleep 30', wenv=self.config())

        # 检查 当前用户
        command = 'whoami'
        self.websocket.send_updates(command)
        current_app.logger.info(command)

        result = self.local.run(command, wenv=self.config())

        # 检查 python 版本
        command = 'python --version'
        result = self.local.run(command, wenv=self.config())
        current_app.logger.info(command)

        # 检查 git 版本
        command = 'git --version'
        result = self.local.run(command, wenv=self.config())
        current_app.logger.info(command)

        # 检查 目录是否存在
        command = 'mkdir -p %s' % (self.dir_codebase_project)
        # TODO remove
        current_app.logger.info(command)
        result = self.local.run(command, wenv=self.config())

        # 用户自定义命令
        command = self.project_info['prev_deploy']
        current_app.logger.info(command)
        with self.local.cd(self.dir_codebase_project):
            result = self.local.run(command, wenv=self.config())

            # SocketHandler.send_to_all({
            #     'type': 'user',
            #     'id': 33,
            #     'host': env.host_string,
            #     'command': command,
            #     'message': result.stdout,
            # })

    def deploy(self):
        '''
        2.检出代码

        :param project_name:
        :return:
        '''
        self.stage = self.stage_deploy
        self.sequence = 2

        current_app.logger.info('git dir: %s', self.dir_codebase_project + '/.git')
        # 如果项目底下有 .git 目录则认为项目完整,可以直接检出代码
        # TODO 不标准
        if os.path.exists(self.dir_codebase_project + '/.git'):
            with self.local.cd(self.dir_codebase_project):
                command = 'pwd && git pull'
                result = self.local.run(command, wenv=self.config())

        else:
            # 否则当作新项目检出完整代码
            with self.local.cd(self.dir_codebase_project):
                command = 'pwd && git clone %s .' % (self.project_info['repo_url'])
                current_app.logger.info('cd %s  command: %s  ', self.dir_codebase_project, command)

                result = self.local.run(command, wenv=self.config())

        # copy to a local version
        self.release_version = '%s_%s_%s' % (
            self.project_name, self.task_id, time.strftime('%Y%m%d_%H%M%S', time.localtime(time.time())))
        with self.local.cd(self.dir_codebase):
            command = 'cp -rf %s %s' % (self.dir_codebase_project, self.release_version)
            current_app.logger.info('cd %s  command: %s  ', self.dir_codebase_project, command)

            result = self.local.run(command, wenv=self.config())

        # 更新到指定 commit_id
        with self.local.cd(self.dir_codebase + self.release_version):
            command = 'git reset -q --hard %s' % (self.taskMdl.get('commit_id'))
            result = self.local.run(command, wenv=self.config())


            # SocketHandler.send_to_all({
            #     'type': 'user',
            #     'id': 33,
            #     'host': env.host_string,
            #     'command': command,
            #     'message': result.stdout,
            # })

            # 用户自定义命令
            # command = self.project_info['deploy']
            # current_app.logger.info(command)
            # with self.local.cd(self.dir_codebase):
            #     result = self.local.run(command)

        pass

    def post_deploy(self):

        '''
        3.检出代码后要做的任务
        - 用户自定义操作命令
        - 代码编译
        - 清除日志文件及无用文件
        -
        - 压缩打包
        - 传送到版本库 release
        :return:
        '''
        self.stage = self.stage_post_deploy
        self.sequence = 3

        # 用户自定义命令
        command = self.project_info['post_deploy']
        current_app.logger.info(command)
        with self.local.cd(self.dir_codebase + self.release_version):
            result = self.local.run(command, wenv=self.config())

        # 压缩打包
        self.release_version_tar = '%s.tgz' % (self.release_version)
        with self.local.cd(self.dir_codebase):
            command = 'tar zcvf %s %s' % (self.release_version_tar, self.release_version)
            result = self.local.run(command, wenv=self.config())

    def prev_release(self, waller):
        '''
        4.部署代码到目标机器前做的任务
        - 检查 webroot 父目录是否存在
        :return:
        '''
        self.stage = self.stage_prev_release
        self.sequence = 4

        # 检查 target_releases 父目录是否存在
        command = 'mkdir -p %s' % (self.project_info['target_releases'])
        result = waller.run(command, wenv=self.config())

        # TODO 检查 webroot 父目录是否存在,是否为软链
        # command = 'mkdir -p %s' % (self.project_info['target_root'])
        # result = waller.run(command)
        # current_app.logger.info('command: %s', dir(result))


        # TODO md5
        # 传送到版本库 release
        current_app.logger.info('/tmp/walle/codebase/' + self.release_version_tar)
        result = waller.put('/tmp/walle/codebase/' + self.release_version_tar,
                            remote=self.project_info['target_releases'])
        current_app.logger.info('command: %s', dir(result))

        # 解压
        self.release_untar(waller)

    def release(self, waller):
        '''
        5.部署代码到目标机器做的任务
        - 打包代码 local
        - scp local => remote
        - 解压 remote
        :return:
        '''
        self.stage = self.stage_release
        self.sequence = 5

        with waller.cd(self.project_info['target_releases']):
            # 1. create a tmp link dir
            current_link_tmp_dir = '%s/current-tmp-%s' % (self.project_info['target_releases'], self.task_id)
            command = 'ln -sfn %s/%s %s' % (
                self.project_info['target_releases'], self.release_version, current_link_tmp_dir)
            result = waller.run(command, wenv=self.config())

            # 2. make a soft link from release to tmp link

            # 3. move tmp link to webroot
            current_link_tmp_dir = '%s/current-tmp-%s' % (self.project_info['target_releases'], self.task_id)
            command = 'mv -fT %s %s' % (current_link_tmp_dir, self.project_info['target_root'])
            result = waller.run(command, wenv=self.config())

    def release_untar(self, waller):
        '''
        解压版本包
        :return:
        '''
        with waller.cd(self.project_info['target_releases']):
            command = 'tar zxf %s' % (self.release_version_tar)
            result = waller.run(command, wenv=self.config())

    def post_release(self, waller):
        '''
        6.部署代码到目标机器后要做的任务
        - 切换软链
        - 重启 nginx
        :return:
        '''
        self.stage = self.stage_post_release
        self.sequence = 6

        self.post_release_service(waller)

    def post_release_service(self, waller):
        '''
        代码部署完成后,服务启动工作,如: nginx重启
        :param connection:
        :return:
        '''

        with waller.cd(self.project_info['target_root']):
            command = 'sudo service nginx restart'
            result = waller.run(command, wenv=self.config())

    def list_tag(self):
        with self.local.cd(self.dir_codebase_project):
            command = 'git tag -l'
            current_app.logger.info('cd %s  command: %s  ', self.dir_codebase_project, command)
            result = self.local.run(command, wenv=self.config())
            current_app.logger.info(dir(result))
            return result

        return None

    def list_branch(self):
        with self.local.cd(self.dir_codebase_project):
            command = 'git pull'
            # result = self.local.run(command, wenv=self.config())

            current_app.logger.info(self.dir_codebase_project)

            command = 'git branch -r'
            result = self.local.run(command, wenv=self.config())

            # TODO 三种可能: false, error, success

            branches = result.stdout.strip().split('\n')
            # 去除 origin/HEAD -> 当前指向
            # 去除远端前缀
            branches = [branch.strip().lstrip('origin/') for branch in branches if not branch.startswith('origin/HEAD')]
            return branches

        return None

    def list_commit(self, branch):
        with self.local.cd(self.dir_codebase_project):
            command = 'git checkout %s && git pull' % (branch)
            result = self.local.run(command, wenv=self.config())

            # TODO 10是需要前端传的
            command = 'git log -10 --pretty="%h #_# %an #_# %s"'
            result = self.local.run(command, wenv=self.config())
            commit_list = result.stdout.strip().split('\n')
            commits = []
            for commit in commit_list:
                commit_dict = commit.split(' #_# ')
                commits.append({
                    'id': commit_dict[0],
                    'name': commit_dict[1],
                    'message': commit_dict[2],
                })
            return commits

        return None

    def walle_deploy(self):


        self.prev_deploy()
        self.deploy()
        self.post_deploy()

        server = '172.16.0.231'
        try:
            self.connections[server] = Waller(host=server, user=self.project_info['target_user'])
            self.prev_release(self.connections[server])
            self.release(self.connections[server])
            self.post_release(self.connections[server])
        except Exception, e:
            current_app.logger.exception(e)
            self.errors[server] = e.message

        # for server_info in self.servers:
        #     server = server_info.host
        #     try:
        #         self.connections[server] = Waller(host=server, user=self.project_info['target_user'])
        #         self.prev_release(self.connections[server])
        #         self.release(self.connections[server])
        #         self.post_release(self.connections[server])
        #     except Exception, e:
        #         self.errors[server] = e.message

        return {'success': self.success, 'errors': self.errors}
