# -*- coding: utf-8 -*-
"""Test Apis."""
from copy import deepcopy

import pytest
from flask import current_app
from .factories import TestApiBase
from .test_00_base import space_base
from .utils import *


@pytest.mark.usefixtures('db')
class TestApiSpace(TestApiBase):
    """api role testing"""
    uri_prefix = '/api/space'

    user_id = {}
    space_default_base = deepcopy(space_base)

    #: user list (1, 2, 3)
    space_data = {
        'name': u'大数据',
        'user_id': u'1',
        'members': json.dumps([{"user_id": 2, "role": "MASTER"}, {"user_id": 3, "role": "DEVELOPER"}]),
    }

    space_name_2 = u'瓦力'

    space_data_2 = {
        'name': u'瓦力',
        'user_id': u'2',
        'members': json.dumps([{"user_id": 3, "role": "MASTER"}, {"user_id": 1, "role": "DEVELOPER"}]),
    }

    space_data_remove = {
        'name': u'瓦尔登',
        'user_id': u'2',
        'members': json.dumps([{"user_id": 1, "role": "MASTER"}, {"user_id": 3, "role": "DEVELOPER"}]),
    }

    # 忘了 user_id 是干嘛的了: (
    # def test_init(self, user, testapp, client, db):
    #     self.init_vars(self.space_data)
    #     self.init_vars(self.space_data_2)
    #     self.init_vars(self.space_data_remove)

    # 初始化 space_id=1的用户列表
    def test_get_update_default_space(self, user, testapp, client):
        """Login successful."""
        # 1.update
        self.space_default_base['members'] = json.dumps(
                [{"user_id": 2, "role": "MASTER"}, {"user_id": 3, "role": "DEVELOPER"}])
        resp = client.put('%s/%d' % (self.uri_prefix, 1), data=self.space_default_base)

        response_success(resp)
        self.compare_member_req_resp(self.space_data, resp)

    def test_create(self, user, testapp, client, db):

        """create successful."""
        # 1.create project
        resp = client.post('%s/' % (self.uri_prefix), data=self.space_data)

        response_success(resp)
        # compare_req_resp(self.space_data, resp)
        current_app.logger.info(resp_json(resp)['data'])
        self.compare_member_req_resp(self.space_data, resp)
        self.space_data['space_id'] = resp_json(resp)['data']['id']

        """create successful."""
        # 1.create another project
        resp = client.post('%s/' % (self.uri_prefix), data=self.space_data_2)
        response_success(resp)

        self.compare_member_req_resp(self.space_data_2, resp)
        self.space_data_2['space_id'] = resp_json(resp)['data']['id']

        # 2.create another space
        # resp = client.post('%s/' % (self.uri_prefix), data=self.space_data_2)
        # space_data_2 = self.get_list_ids(self.space_data_2)
        #
        # response_success(resp)
        # compare_req_resp(space_data_2, resp)
        # self.space_data_2['space_id'] = resp_json(resp)['data']['space_id']

    def test_one(self, user, testapp, client, db):
        """item successful."""
        resp = client.get('%s/%d' % (self.uri_prefix, self.space_data['space_id']))

        response_success(resp)
        self.compare_member_req_resp(self.space_data, resp)

        # def test_get_list_page_size(self, user, testapp, client):
        #     """test list should create 2 users at least, due to test pagination, searching."""
        #
        #     query = {
        #         'page': 1,
        #         'size': 1,
        #     }
        #     response = {
        #         'count': 2,
        #     }
        #     resp = client.get('%s/?%s' % (self.uri_prefix, urlencode(query)))
        #     response_success(resp)
        #     resp_dict = resp_json(resp)
        #
        #     res = resp_dict['data']['list'].pop()
        #     # f.write(str(res))
        #
        #     # compare_in(self.space_data_2, resp_dict['data']['list'].pop())
        #     space_data_2 = self.get_list_ids(self.space_data_2)
        #     del space_data_2['user_id']
        #
        #     compare_in(space_data_2, res)
        #     compare_req_resp(response, resp)
        #
        # def test_get_list_query(self, user, testapp, client):
        #     """test list should create 2 users at least, due to test pagination, searching."""
        #     query = {
        #         'page': 1,
        #         'size': 1,
        #         'kw': self.space_name_2
        #     }
        #     response = {
        #         'count': 1,
        #     }
        #     resp = client.get('%s/?%s' % (self.uri_prefix, urlencode(query)))
        #     response_success(resp)
        #     resp_dict = resp_json(resp)
        #     space_data_2 = self.get_list_ids(self.space_data_2)
        #     del space_data_2['user_id']
        #
        #     compare_in(space_data_2, resp_dict['data']['list'].pop())
        #     compare_req_resp(response, resp)
        #

    def test_get_update(self, user, testapp, client):
        """Login successful."""
        # 1.update
        space_data = self.space_data
        space_data['name'] = u'大数据平台'
        resp = client.put('%s/%d' % (self.uri_prefix, self.space_data['space_id']), data=space_data)

        response_success(resp)
        self.compare_member_req_resp(self.space_data, resp)

        # 1.update
        space_data_2 = self.space_data_2
        space_data_2['name'] = u'瓦力2.0'
        resp = client.put('%s/%d' % (self.uri_prefix, self.space_data_2['space_id']), data=space_data_2)

        response_success(resp)
        self.compare_member_req_resp(self.space_data_2, resp)

        # 2.get it
        resp = client.get('%s/%d' % (self.uri_prefix, self.space_data_2['space_id']))
        response_success(resp)

        response_success(resp)
        self.compare_member_req_resp(self.space_data_2, resp)

        # def test_get_remove(self, user, testapp, client):
        #     """Login successful."""
        #     # 1.create another role
        #     resp = client.post('%s/' % (self.uri_prefix), data=self.space_data_remove)
        #     space_id = resp_json(resp)['data']['space_id']
        #     response_success(resp)
        #
        #     # 2.delete
        #     resp = client.delete('%s/%d' % (self.uri_prefix, space_id))
        #     response_success(resp)
        #
        #     # 3.get it
        #     resp = client.get('%s/%d' % (self.uri_prefix, space_id))
        #     response_error(resp)

    def compare_member_req_resp(self, request, response):
        for user_response in resp_json(response)['data']['members']:
            for user_request in json.loads(request['members']):
                if user_request['user_id'] == user_response['user_id']:
                    assert user_request['role'] == user_response['role']
