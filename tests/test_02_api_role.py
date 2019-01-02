# -*- coding: utf-8 -*-
"""Test Apis."""
import pytest
from .factories import TestApiBase
from .utils import *


@pytest.mark.usefixtures('db')
class TestApiRole(TestApiBase):
    """api role testing"""
    uri_prefix = '/api/role'

    # role_data = {
    #     'role_name': u'研发组',
    #     'access_ids': '1,3',
    # }
    #
    # role_name_2 = u'Test Leader'
    #
    # role_data_2 = {
    #     'role_name': u'Test Leader',
    #     'access_ids': '1,2',
    # }

    # def test_create(self, user, testapp, client, db):
    #     """create successful."""
    #     # 1.create another role
    #     resp = client.post('%s/' % (self.uri_prefix), data=self.role_data)
    #
    #     response_success(resp)
    #     compare_req_resp(self.role_data, resp)
    #     self.role_data['id'] = resp_json(resp)['data']['id']
    #
    #     # 2.create another role
    #     resp = client.post('%s/' % (self.uri_prefix), data=self.role_data_2)
    #     self.role_data_2['id'] = resp_json(resp)['data']['id']
    #
    #
    #     response_success(resp)
    #     compare_req_resp(self.role_data_2, resp)

    # def test_one(self, user, testapp, client, db):
    #     """item successful."""
    #     # Goes to homepage
    #     resp = client.get('%s/master' % (self.uri_prefix, self.role_data['id']))
    #
    #     response_success(resp)
    #     compare_req_resp(self.role_data, resp)

    def test_get_list_page_size(self, user, testapp, client):
        """test list should create 2 users at least, due to test pagination, searching."""

        query = {
            'page': 1,
            'size': 1,
        }
        response = {
            'count': 5,
        }
        resp = client.get('%s/' % (self.uri_prefix))
        response_success(resp)

        compare_req_resp(response, resp)
        #
        # def test_get_list_query(self, user, testapp, client):
        #     """test list should create 2 users at least, due to test pagination, searching."""
        #     query = {
        #         'page': 1,
        #         'size': 1,
        #         'kw': self.role_name_2
        #     }
        #     response = {
        #         'count': 1,
        #     }
        #     resp = client.get('%s/?%s' % (self.uri_prefix, urlencode(query)))
        #     response_success(resp)
        #     resp_dict = resp_json(resp)
        #
        #     compare_in(self.role_data_2, resp_dict['data']['list'].pop())
        #     compare_req_resp(response, resp)

        # def test_get_update(self, user, testapp, client):
        #     """Login successful."""
        #     # 1.create another role
        #     # resp = client.post('%s/' % (self.uri_prefix), data=self.role_data)
        #     # role_id = resp_json(resp)['data']['id']
        #     #
        #     # response_success(resp)
        #     # compare_req_resp(self.role_data, resp)
        #
        #     # 2.update
        #     resp = client.put('%s/%d' % (self.uri_prefix, self.role_data_2['id']), data=self.role_data_2)
        #
        #     response_success(resp)
        #     compare_req_resp(self.role_data_2, resp)
        #
        #     # 3.get it
        #     resp = client.get('%s/%d' % (self.uri_prefix, self.role_data_2['id']))
        #     response_success(resp)
        #     compare_req_resp(self.role_data_2, resp)
        #
        # def test_get_remove(self, user, testapp, client):
        #     """Login successful."""
        #     # 1.create another role
        #     another_role = self.role_data_2
        #     another_role['role_name'] = u'To Be Removed'
        #     resp = client.post('%s/' % (self.uri_prefix), data=another_role)
        #     role_id = resp_json(resp)['data']['id']
        #
        #     response_success(resp)
        #
        #     # 2.delete
        #     resp = client.delete('%s/%d' % (self.uri_prefix, role_id))
        #
        #     # 3.get it
        #     resp = client.get('%s/%d' % (self.uri_prefix, role_id))
        #     response_error(resp)
