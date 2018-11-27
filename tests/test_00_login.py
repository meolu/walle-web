# -*- coding: utf-8 -*-
"""Test Apis."""
import urllib

import pytest

from .utils import *
from walle.model.user import UserModel
from copy import deepcopy
from .test_00_base import user_data_login

#: 4 登录 owner
@pytest.mark.usefixtures('db')
class TestApiPassport:
    """api role testing"""
    uri_prefix = '/api/passport'

    user_id = {}

    user_data = deepcopy(user_data_login)


    def test_fetch(self):
        u = UserModel.get_by_id(2)

    def test_login(self, user, testapp, client, db):
        """create successful."""

        resp = client.post('%s/login' % (self.uri_prefix), data=self.user_data)

        response_success(resp)

        del self.user_data['password']
        compare_req_resp(self.user_data, resp)
