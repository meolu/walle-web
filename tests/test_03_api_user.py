# -*- coding: utf-8 -*-
"""Test Apis."""
import urllib

import pytest
from flask import current_app
from .utils import *

user_data = {
    'email': u'test01@walle-web.io',
    'password': u'Walle987',
    'username': u'测试用户',
}


@pytest.mark.usefixtures('db')
class TestApiUser:
    """api role testing"""
    uri_prefix = '/api/user'

    user_id = {}

    user_data = user_data

    user_name_2 = u'Tester'

    user_data_error = {
        'email': u'user_error@walle-web.io',
        'password': u'walle99',
        'username': u'Tester',
    }

    user_data_2 = {
        'email': u'test02@walle-web.io',
        'password': u'Walle99999',
        'username': u'Tester',
    }

    user_data_3 = {
        'email': u'test03@walle-web.io',
        'password': u'Walle99999',
        'username': u'waller03',
    }

    user_data_4 = {
        'email': u'test04@walle-web.io',
        'password': u'Walle99999',
        'username': u'waller04',
    }

    user_data_remove = {
        'email': u'test_remove@walle-web.io',
        'password': u'Walle987&^*',
        'username': u'test_remove',
    }

    def test_create(self, user, testapp, client, db):
        """create successful."""

        # 1.error
        resp = client.post('%s/' % (self.uri_prefix), data=self.user_data_error)
        response_error(resp)

        # 2.create another user
        resp = client.post('%s/' % (self.uri_prefix), data=self.user_data)

        response_success(resp)

        del self.user_data['password']
        compare_req_resp(self.user_data, resp)
        self.user_data['id'] = resp_json(resp)['data']['id']

        # 3.create another user
        resp = client.post('%s/' % (self.uri_prefix), data=self.user_data_2)

        response_success(resp)
        del self.user_data_2['password']

        compare_req_resp(self.user_data_2, resp)
        self.user_data_2['id'] = resp_json(resp)['data']['id']

        # 4.create another user
        resp = client.post('%s/' % (self.uri_prefix), data=self.user_data_3)
        del self.user_data_3['password']

        # 5.create another user
        resp = client.post('%s/' % (self.uri_prefix), data=self.user_data_4)
        del self.user_data_4['password']

    def test_one(self, user, testapp, client, db):
        """item successful."""
        # Goes to homepage

        resp = client.get('%s/%d' % (self.uri_prefix, self.user_data['id']))

        response_success(resp)
        compare_req_resp(self.user_data, resp)

    def test_get_list_page_size(self, user, testapp, client):
        """test list should create 2 users at least, due to test pagination, searching."""

        query = {
            'page': 1,
            'size': 1,
        }
        response = {
            'count': 7,
        }
        resp = client.get('%s/?%s' % (self.uri_prefix, urlencode(query)))
        response_success(resp)
        resp_dict = resp_json(resp)

        compare_in(self.user_data_4, resp_dict['data']['list'].pop())
        compare_req_resp(response, resp)

    def test_get_list_query(self, user, testapp, client):
        """test list should create 2 users at least, due to test pagination, searching."""
        query = {
            'page': 1,
            'size': 1,
            'kw': self.user_name_2
        }
        response = {
            'count': 1,
        }
        resp = client.get('%s/?%s' % (self.uri_prefix, urlencode(query)))
        response_success(resp)
        resp_dict = resp_json(resp)

        compare_in(self.user_data_2, resp_dict['data']['list'].pop())
        compare_req_resp(response, resp)

    def test_get_update(self, user, testapp, client):
        """Login successful."""

        # 2.update
        user_data_2 = self.user_data_2
        user_data_2['username'] = 'Tester_edit'
        current_app.logger.error(user_data_2)
        resp = client.put('%s/%d' % (self.uri_prefix, self.user_data_2['id']), data=user_data_2)

        response_success(resp)
        compare_req_resp(user_data_2, resp)

        # 3.get it
        resp = client.get('%s/%d' % (self.uri_prefix, self.user_data_2['id']))
        response_success(resp)
        compare_req_resp(user_data_2, resp)

    def test_get_remove(self, user, testapp, client):
        """Login successful."""
        # 1.create another role
        resp = client.post('%s/' % (self.uri_prefix), data=self.user_data_remove)
        user_id = resp_json(resp)['data']['id']
        response_success(resp)

        # 2.delete
        resp = client.delete('%s/%d' % (self.uri_prefix, user_id))
        response_success(resp)

        # 3.get it
        resp = client.get('%s/%d' % (self.uri_prefix, user_id))
        response_error(resp)
