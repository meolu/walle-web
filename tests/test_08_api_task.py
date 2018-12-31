# -*- coding: utf-8 -*-
"""Test Apis."""
import urllib

from walle.model.task import TaskModel
from .factories import TestApiBase
from .utils import *


class TestApiTask(TestApiBase):
    """api role testing"""
    uri_prefix = '/api/task'

    server_id = {}

    # TODO 需要再准备一个是否需要开启审核的status单测

    task_data = {
        'name': u'提交一个测试上线单',
        'project_id': 1,
        'servers': u'127.0.0.1,192.168.0.1',
        'commit_id': u'a89eb23c',
        'branch': u'master',
        'file_transmission_mode': 0,
        'file_list': u'*.log'
    }

    # should be equal to task_data_2.name
    task_name_2 = u'The Second bill'

    task_data_2 = {
        'name': u'The Second Bill',
        'project_id': 1,
        'servers': u'1,2',
        'commit_id': u'a89eb23c',
        'branch': u'master',
        'file_transmission_mode': 0,
        'file_list': u'*.log'
    }

    task_data_2_update = {
        'name': u'The Second Bill Edit',
        'project_id': 1,
        'servers': u'1,2',
        'commit_id': u'a89eb23c',
        'branch': u'master',
        'file_transmission_mode': 0,
        'file_list': u'*.log,*.txt'
    }

    task_data_remove = {
        'name': u'A Task To Be Removed',
        'project_id': 1,
        'servers': u'1,2,3',
        'commit_id': u'a89eb23c',
        'branch': u'master',
        'file_transmission_mode': 0,
        'file_list': u'*.log'
    }

    def test_create(self, user, testapp, client, db):
        """create successful."""
        # 1.create another role
        resp = client.post('%s/' % (self.uri_prefix), data=self.task_data)

        response_success(resp)
        compare_req_resp(self.task_data, resp)

        self.task_data['id'] = resp_json(resp)['data']['id']

        # 2.create another role
        resp = client.post('%s/' % (self.uri_prefix), data=self.task_data_2)

        response_success(resp)
        compare_req_resp(self.task_data_2, resp)

        self.task_data_2['id'] = resp_json(resp)['data']['id']

    def test_one(self, user, testapp, client, db):
        """item successful."""
        # Goes to homepage

        resp = client.get('%s/%d' % (self.uri_prefix, self.task_data['id']))

        response_success(resp)
        compare_req_resp(self.task_data, resp)

    def test_get_list_page_size(self, user, testapp, client):
        """test list should create 2 users at least, due to test pagination, searching."""

        query = {
            'page': 1,
            'size': 1,
        }
        response = {
            'count': 2,
        }
        resp = client.get('%s/?%s' % (self.uri_prefix, urlencode(query)))
        response_success(resp)
        resp_dict = resp_json(resp)

        compare_in(self.task_data_2, resp_dict['data']['list'].pop())
        compare_req_resp(response, resp)

    def test_get_list_query(self, user, testapp, client):
        """test list should create 2 users at least, due to test pagination, searching."""
        query = {
            'page': 1,
            'size': 1,
            'kw': self.task_name_2
        }
        response = {
            'count': 1,
        }
        resp = client.get('%s/?%s' % (self.uri_prefix, urlencode(query)))
        response_success(resp)
        resp_dict = resp_json(resp)

        compare_in(self.task_data_2, resp_dict['data']['list'].pop())
        compare_req_resp(response, resp)

    def test_get_update(self, user, testapp, client):
        """Login successful."""
        # 1.update
        resp = client.put('%s/%d' % (self.uri_prefix, self.task_data_2['id']), data=self.task_data_2_update)

        response_success(resp)
        compare_req_resp(self.task_data_2_update, resp)

        # 3.get it
        resp = client.get('%s/%d' % (self.uri_prefix, self.task_data_2['id']))
        response_success(resp)
        compare_req_resp(self.task_data_2_update, resp)

    def test_get_update_audit(self, user, testapp, client):
        """Login successful."""
        # 1.update
        resp = client.put('%s/%d/audit' % (self.uri_prefix, self.task_data_2['id']))

        response_success(resp)
        assert resp_json(resp)['data']['status'] == TaskModel.status_pass

    def test_get_update_reject(self, user, testapp, client):
        """Login successful."""
        # 1.update
        resp = client.put('%s/%d/reject' % (self.uri_prefix, self.task_data_2['id']))

        response_success(resp)
        assert resp_json(resp)['data']['status'] == TaskModel.status_reject

    def test_get_remove(self, user, testapp, client):
        """Login successful."""
        # 1.create another role
        resp = client.post('%s/' % (self.uri_prefix), data=self.task_data_remove)
        server_id = resp_json(resp)['data']['id']
        response_success(resp)

        # 2.delete
        resp = client.delete('%s/%d' % (self.uri_prefix, server_id))
        response_success(resp)

        # 3.get it
        resp = client.get('%s/%d' % (self.uri_prefix, server_id))
        response_error(resp)
