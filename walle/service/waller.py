#!/usr/bin/env python
# -*- coding: utf-8 -*-
# @Author: wushuiyong
# @Created Time : 日  1/ 1 23:43:12 2017
# @Description:

from fabric2 import Connection
from flask import current_app
from walle.model.deploy import TaskRecordModel
from flask_socketio import SocketIO, emit




class Waller(Connection):
    connections, success, errors = {}, {}, {}
    release_version_tar, release_version = None, None

    def run(self, command, wenv=None, sudo=False, **kwargs):
        '''
        # TODO
        pty=True/False是直接影响到输出.False较适合在获取文本,True更适合websocket

        :param command:
        :param wenv:
        :param sudo:
        :param kwargs:
        :return:
        '''
        try:
            message = 'task_id=%s, host:%s command:%s' % (
                wenv['task_id'], self.host, command
            )
            current_app.logger.info(message)
            if sudo:
                result = super(Waller, self).sudo(command, pty=False, **kwargs)
            else:
                result = super(Waller, self).run(command, pty=False, **kwargs)

            message = 'task_id=%s, host:%s command:%s status:%s, success:%s, error:%s' % (
                wenv['task_id'], self.host, command, result.exited, result.stdout.strip(), result.stderr.strip()
            )
            emit('message', {'msg': command + result.stdout.strip()})

            # TODO
            if wenv.has_key('websocket') and wenv['websocket']:

                ws_dict = {
                    'host': self.host,
                    'cmd': command,
                    'status': result.exited,
                    'stage': wenv['stage'],
                    'sequence': wenv['sequence'],
                    'success': result.stdout.strip(),
                    'error': result.stderr.strip(),
                }
                emit('message', {'msg': 'DeploySocketIO.' + str(ws_dict)})
                # wenv['websocket'].send_updates(ws_dict)
            TaskRecordModel().save_record(stage=wenv['stage'], sequence=wenv['sequence'], user_id=wenv['user_id'],
                                          task_id=wenv['task_id'], status=result.exited, host=self.host, user=self.user,
                                          command=result.command,success=result.stdout.strip(), error=result.stderr.strip())
            current_app.logger.info(message)
            return result

        except Exception, e:
            #current_app.logger.exception(e)
            #return None
            # TODO 貌似可能的异常有很多种，需要分层才能完美解决 something wrong without e.result
            TaskRecordModel().save_record(stage=wenv['stage'], sequence=wenv['sequence'], user_id=wenv['user_id'],
                                          task_id=wenv['task_id'], status=1, host=self.host, user=self.user,
                                          command=command, success='', error='e.result')
            if hasattr(e, 'resean') and hasattr(e, 'result'):
                message = 'task_id=%s, host:%s command:%s, status=1, reason:%s, result:%s' % (
                    wenv['task_id'], self.host, command, e.reason, e.result
                )
            else:
                message = 'task_id=%s, host:%s command:%s, status=1, message:%s' % (
                    wenv['task_id'], self.host, command, e.message
                )

            # TODO
            if wenv.has_key('websocket') and wenv['websocket']:

                ws_dict = {
                    'host': self.host,
                    'cmd': command,
                    'status': 1,
                    'stage': wenv['stage'],
                    'sequence': wenv['sequence'],
                    'success': '',
                    'error': e.message,
                }
                # wenv['websocket'].send_updates(ws_dict)
            # current_app.logger.error(message)

            return False

    def sudo(self, command, wenv=None, **kwargs):
        return self.run(command, wenv=wenv, sudo=True, **kwargs)

    def get(self, remote, local=None, wenv=None):
        return self.sync(wtype='get', remote=remote, local=local, wenv=wenv)

    def put(self, local, remote=None, wenv=None, *args, **kwargs):
        return self.sync(wtype='put', local=local, remote=remote, wenv=wenv, *args, **kwargs)

    def sync(self, wtype, remote=None, local=None, wenv=None):
        try:
            if wtype == 'put':
                result = super(Waller, self).put(local=local, remote=remote)
                command = 'put: scp %s %s@%s:%s' % (result.local, self.user, self.host, result.remote)
                current_app.logger.info('put: local %s, remote %s', local, remote)

            else:
                result = super(Waller, self).get(remote=remote, local=local)
                command = 'get: scp %s@%s:%s %s' % (self.user, self.host, result.remote, result.local)
                current_app.logger.info('get: local %s, remote %s', local, remote)
                current_app.logger.info('get: orig_local %s, local %s', result.orig_local, result.local)

            current_app.logger.info('put: %s, %s', result, dir(result))
            # TODO 可能会有非22端口的问题
            TaskRecordModel().save_record(stage=wenv['stage'], sequence=wenv['sequence'], user_id=wenv['user_id'],
                                          task_id=wenv['task_id'], status=0, host=self.host, user=self.user,
                                          command=command, )
            message = 'task_id=%d, host:%s command:%s status:0, success:, error:' % (
            wenv['task_id'], self.host, command)
            current_app.logger.info(message)

            # TODO
            if wenv.has_key('websocket') and wenv['websocket']:

                ws_dict = {
                    'host': self.host,
                    'cmd': command,
                    'status': 1,
                    'stage': wenv['stage'],
                    'sequence': wenv['sequence'],
                    'success': '',
                    'error': result.stderr.strip(),
                }
                # wenv['websocket'].send_updates(ws_dict)

            return result
        except Exception, e:
            # TODO 收尾下
            current_app.logger.info('put: %s, %s', e, dir(e))


            # TODO
            if wenv.has_key('websocket') and wenv['websocket']:

                # TODO command
                ws_dict = {
                    'host': self.host,
                    'cmd': 'command',
                    'status': 1,
                    'stage': wenv['stage'],
                    'sequence': wenv['sequence'],
                    'success': '',
                    'error': e.message,
                }
                # wenv['websocket'].send_updates(ws_dict)
