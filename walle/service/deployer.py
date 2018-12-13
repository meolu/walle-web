#!/usr/bin/env python
# -*- coding: utf-8 -*-
# @Author: wushuiyong
# @Created Time : 日  1/ 1 23:43:12 2017
# @Description:


import time
from datetime import datetime

import os
import re
from flask import current_app
from walle.model.project import ProjectModel
from walle.model.record import RecordModel
from walle.model.task import TaskModel
from walle.service.code import Code
from walle.service.error import WalleError
from walle.service.extensions import socketio
from walle.service.utils import color_clean
from walle.service.waller import Waller


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

    console = False

    version = datetime.now().strftime('%Y%m%d%H%M%s')

    local_codebase, dir_codebase_project, project_name = None, None, None
    dir_release, dir_webroot = None, None

    connections, success, errors = {}, {}, {}
    release_version_tar, release_version = None, None
    local = None

    def __init__(self, task_id=None, project_id=None, console=False):
        self.local_codebase = current_app.config.get('CODE_BASE')
        self.local = Waller(host=current_app.config.get('LOCAL_SERVER_HOST'),
                            user=current_app.config.get('LOCAL_SERVER_USER'),
                            port=current_app.config.get('LOCAL_SERVER_PORT'),
                            )
        self.TaskRecord = RecordModel()

        if task_id:
            self.task_id = task_id
            # task start
            current_app.logger.info(self.task_id)
            self.taskMdl = TaskModel().item(self.task_id)
            self.user_id = self.taskMdl.get('user_id')
            self.servers = self.taskMdl.get('servers_info')
            self.task = self.taskMdl.get('target_user')
            self.project_info = self.taskMdl.get('project_info')

        if project_id:
            self.project_id = project_id
            self.project_info = ProjectModel(id=project_id).item()

        self.project_name = self.project_info['id']
        self.dir_codebase_project = self.local_codebase + str(self.project_name)

        # start to deploy
        self.console = console

    def config(self):
        return {'task_id': self.task_id, 'user_id': self.user_id, 'stage': self.stage, 'sequence': self.sequence,
                'console': self.console}

    def start(self):
        TaskModel().get_by_id(self.task_id).update({'status': TaskModel.status_doing})
        self.taskMdl = TaskModel().item(self.task_id)

    # ===================== fabric ================
    # SocketHandler
    def prev_deploy(self):
        '''
        # TODO
        socketio.sleep(0.001)
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

        # 检查 当前用户
        command = 'whoami'
        current_app.logger.info(command)
        result = self.local.run(command, wenv=self.config())

        # 检查 python 版本
        command = 'python --version'
        result = self.local.run(command, wenv=self.config())

        # 检查 git 版本
        command = 'git --version'
        result = self.local.run(command, wenv=self.config())

        # 检查 目录是否存在
        self.init_repo()

        # TODO to be removed
        command = 'mkdir -p %s' % (self.dir_codebase_project)
        result = self.local.run(command, wenv=self.config())

        # 用户自定义命令
        command = self.project_info['prev_deploy']
        current_app.logger.info(command)
        with self.local.cd(self.dir_codebase_project):
            result = self.local.run(command, wenv=self.config())

    def deploy(self):
        '''
        2.检出代码

        :param project_name:
        :return:
        '''
        # TODO
        socketio.sleep(0.001)
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
        with self.local.cd(self.local_codebase + self.release_version):
            command = 'git reset -q --hard %s' % (self.taskMdl.get('commit_id'))
            result = self.local.run(command, wenv=self.config())

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
        # TODO
        socketio.sleep(0.001)
        self.stage = self.stage_post_deploy
        self.sequence = 3

        # 用户自定义命令
        command = self.project_info['post_deploy']
        with self.local.cd(self.local_codebase + self.release_version):
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
        # TODO
        socketio.sleep(0.001)
        self.stage = self.stage_prev_release
        self.sequence = 4

        # 检查 target_releases 父目录是否存在
        command = 'mkdir -p %s' % (self.project_info['target_releases'])
        result = waller.run(command, wenv=self.config())

        # TODO 检查 webroot 父目录是否存在,是否为软链
        # command = 'mkdir -p %s' % (self.project_info['target_root'])
        # result = waller.run(command)
        # current_app.logger.info('command: %s', dir(result))

        # 用户自定义命令
        command = self.project_info['prev_release']
        current_app.logger.info(command)
        with waller.cd(self.project_info['target_releases']):
            result = waller.run(command, wenv=self.config())

        # TODO md5
        # 传送到版本库 release
        current_app.logger.info('/tmp/walle/codebase/' + self.release_version_tar)
        result = waller.put('/tmp/walle/codebase/' + self.release_version_tar,
                            remote=self.project_info['target_releases'], wenv=self.config())
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
        # TODO
        socketio.sleep(0.001)
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
        # TODO
        socketio.sleep(0.001)
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

        # 用户自定义命令
        command = self.project_info['post_release']
        current_app.logger.info(command)
        with waller.cd(self.project_info['target_root']):
            result = waller.run(command, wenv=self.config())

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
        self.init_repo()

        with self.local.cd(self.dir_codebase_project):
            command = 'git tag -l'
            result = self.local.run(command, wenv=self.config())
            tags = color_clean(result.stdout.strip())
            tags = tags.split('\n')
            return [color_clean(tag.strip()) for tag in tags]

        return None

    def list_branch(self):
        self.init_repo()

        with self.local.cd(self.dir_codebase_project):
            command = 'git pull'
            result = self.local.run(command, wenv=self.config())

            if result.exited != Code.Ok:
                raise WalleError(Code.shell_git_pull_fail)

            current_app.logger.info(self.dir_codebase_project)

            command = 'git branch -r'
            result = self.local.run(command, wenv=self.config())

            # if result.exited != Code.Ok:
            #     raise WalleError(Code.shell_run_fail)

            # TODO 三种可能: false, error, success
            branches = color_clean(result.stdout.strip())
            branches = branches.split('\n')
            # 去除 origin/HEAD -> 当前指向
            # 去除远端前缀
            branches = [branch.strip().lstrip('origin/') for branch in branches if
                        not branch.strip().startswith('origin/HEAD')]
            return branches

        return None

    def list_commit(self, branch):
        self.init_repo()

        with self.local.cd(self.dir_codebase_project):
            command = 'git checkout %s && git pull' % (branch)
            self.local.run(command, wenv=self.config())

            command = 'git log -35 --pretty="%h #_# %an #_# %s"'
            result = self.local.run(command, wenv=self.config())
            current_app.logger.info(result.stdout)

            commit_log = color_clean(result.stdout.strip())
            current_app.logger.info(commit_log)
            commit_list = commit_log.split('\n')
            commits = []
            for commit in commit_list:
                if not re.search('^.+ #_# .+ #_# .*$', commit):
                    continue

                commit_dict = commit.split(' #_# ')
                current_app.logger.info(commit_dict)
                commits.append({
                    'id': commit_dict[0],
                    'name': commit_dict[1],
                    'message': commit_dict[2],
                })

            return commits

        # TODO
        return None

    def init_repo(self):
        if os.path.exists(self.dir_codebase_project):
            # 检查 目录是否存在
            command = 'mkdir -p %s' % (self.dir_codebase_project)
            # TODO remove
            current_app.logger.info(command)
            self.local.run(command, wenv=self.config())

        with self.local.cd(self.dir_codebase_project):
            is_git_dir = self.local.run('git status', wenv=self.config())
        if is_git_dir.exited != Code.Ok:
            # 否则当作新项目检出完整代码
            # 检查 目录是否存在
            command = 'rm -rf %s' % (self.dir_codebase_project)
            self.local.run(command, wenv=self.config())

            command = 'git clone %s %s' % (self.project_info['repo_url'], self.dir_codebase_project)
            current_app.logger.info('cd %s  command: %s  ', self.dir_codebase_project, command)

            result = self.local.run(command, wenv=self.config())
            if result.exited != Code.Ok:
                raise WalleError(Code.shell_git_init_fail, message=result.stdout)

    def end(self, success=True):
        status = TaskModel.status_success if success else TaskModel.status_fail
        TaskModel().get_by_id(self.task_id).update({'status': status})

    def walle_deploy(self):
        self.start()
        self.prev_deploy()
        self.deploy()
        self.post_deploy()

        all_servers_success = True
        for server_info in self.servers:
            server = server_info['host']
            try:
                self.connections[server] = Waller(host=server, user=self.project_info['target_user'])
                self.prev_release(self.connections[server])
                self.release(self.connections[server])
                self.post_release(self.connections[server])
            except Exception as e:
                current_app.logger.error(e)
                all_servers_success = False
                self.errors[server] = e.message

        self.end(all_servers_success)
        return {'success': self.success, 'errors': self.errors}
