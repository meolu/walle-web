# -*- coding: utf-8 -*-
"""Test Apis."""
from flask import json
import types
import urllib
import pytest
from .utils import *
from .test_03_api_user import user_data
from .test_00_base import user_data_login
from copy import deepcopy

@pytest.mark.usefixtures('db')
class TestApiPassport:
    """api role testing"""
    uri_prefix = '/api/passport'

    user_id = {}

    user_data = user_data
    user_data_login = deepcopy(user_data_login)

    user_name = u'test01@walle-web.io'

    def test_login(self, user, testapp, client, db):
        """create successful."""
        # 1.create another role
        query = {
            'page': 1,
            'size': 1,
            'kw': self.user_name
        }
        response = {
            'count': 1,
        }
        resp = client.get('/api/user/?%s' % (urlencode(query)))
        response_success(resp)
        compare_req_resp(response, resp)

        resp = client.post('%s/login' % (self.uri_prefix), data=self.user_data_login)

        response_success(resp)

        del self.user_data_login['password']
        compare_req_resp(self.user_data_login, resp)
