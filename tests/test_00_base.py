# -*- coding: utf-8 -*-
"""Model unit tests."""

from copy import deepcopy

import pytest
from flask import current_app
from walle.model.menu import MenuModel
from walle.model.user import UserModel
from walle.service.rbac.role import *
from werkzeug.security import generate_password_hash
from .utils import *

#: 1 创建 super, owner

#: 2 登录 super

#: 3 创建 space, users

#: 4 登录 owner

user_super = {
    'username': u'super',
    'email': u'Super@walle-web.io',
    'password': u'WU123shuiyong',
}

user_owner = {
    'username': u'owner',
    'email': u'Owner@walle-web.io',
    'password': u'WU123shuiyong',
}

user_data_login = user_owner

space_base = {
    'name': u'walle-2.0',
    'user_id': 1,
}


@pytest.mark.usefixtures('db')
class TestFoo:
    """User tests."""

    def test_get_by_id(self):
        """Get user by ID."""
        pass
        # user = Foo(username='testuser', email='wushuiyong@mail.com')
        # user.save()
        # print(user.id)
        #
        # retrieved = Foo.get_by_id(user.id)
        # assert retrieved == user


class TestAccess:
    def from_data(self):
        return []

    def test_add(self):
        access_list = [
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:11:38",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 1,
                "name_cn": u"用户中心",
                "name_en": u"",
                "pid": 0,
                "sequence": 10001,
                "type": u"module",
                "updated_at": u"2017-06-12 00:15:29"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:11:52",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 2,
                "name_cn": u"配置中心",
                "name_en": u"",
                "pid": 0,
                "sequence": 10002,
                "type": u"module",
                "updated_at": u"2017-06-12 00:15:29"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:12:45",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 3,
                "name_cn": u"上线单",
                "name_en": u"",
                "pid": 0,
                "sequence": 10003,
                "type": u"module",
                "updated_at": u"2017-06-12 00:15:29"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:13:51",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 11,
                "name_cn": u"用户管理",
                "name_en": u"user",
                "pid": 1,
                "sequence": 10101,
                "type": u"controller",
                "updated_at": u"2017-06-14 10:42:45"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:14:11",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 12,
                "name_cn": u"用户组",
                "name_en": u"group",
                "pid": 1,
                "sequence": 10102,
                "type": u"controller",
                "updated_at": u"2017-06-14 10:42:48"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:14:44",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 13,
                "name_cn": u"角色",
                "name_en": u"role",
                "pid": 1,
                "sequence": 10103,
                "type": u"controller",
                "updated_at": u"2017-06-14 10:42:52"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:15:30",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 14,
                "name_cn": u"环境管理",
                "name_en": u"environment",
                "pid": 2,
                "sequence": 10201,
                "type": u"controller",
                "updated_at": u"2017-06-14 10:42:58"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:15:51",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 15,
                "name_cn": u"服务器管理",
                "name_en": u"server",
                "pid": 2,
                "sequence": 10202,
                "type": u"controller",
                "updated_at": u"2017-06-14 10:43:01"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:16:18",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 16,
                "name_cn": u"项目管理",
                "name_en": u"project",
                "pid": 2,
                "sequence": 10203,
                "type": u"controller",
                "updated_at": u"2017-06-14 10:43:07"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:17:12",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 101,
                "name_cn": u"查看",
                "name_en": u"get",
                "pid": 11,
                "sequence": 11101,
                "type": u"action",
                "updated_at": u"2017-06-14 10:43:09"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:17:26",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 102,
                "name_cn": u"修改",
                "name_en": u"put",
                "pid": 11,
                "sequence": 11102,
                "type": u"action",
                "updated_at": u"2017-06-14 10:43:17"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:17:59",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 103,
                "name_cn": u"新增",
                "name_en": u"post",
                "pid": 11,
                "sequence": 11103,
                "type": u"action",
                "updated_at": u"2017-06-14 10:43:19"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-11 23:18:16",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 104,
                "name_cn": u"删除",
                "name_en": u"delete",
                "pid": 11,
                "sequence": 11104,
                "type": u"action",
                "updated_at": u"2017-06-14 10:43:35"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:14:56",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 105,
                "name_cn": u"查看",
                "name_en": u"get",
                "pid": 12,
                "sequence": 11201,
                "type": u"action",
                "updated_at": u"2017-06-19 08:14:56"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:14:56",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 106,
                "name_cn": u"修改",
                "name_en": u"put",
                "pid": 12,
                "sequence": 11202,
                "type": u"action",
                "updated_at": u"2017-06-19 08:14:56"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:14:56",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 107,
                "name_cn": u"新增",
                "name_en": u"post",
                "pid": 12,
                "sequence": 11203,
                "type": u"action",
                "updated_at": u"2017-06-19 08:14:56"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:14:56",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 108,
                "name_cn": u"删除",
                "name_en": u"delete",
                "pid": 12,
                "sequence": 11204,
                "type": u"action",
                "updated_at": u"2017-06-19 08:14:56"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:15:22",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 109,
                "name_cn": u"查看",
                "name_en": u"get",
                "pid": 13,
                "sequence": 11301,
                "type": u"action",
                "updated_at": u"2017-06-19 08:15:22"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:15:22",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 110,
                "name_cn": u"修改",
                "name_en": u"put",
                "pid": 13,
                "sequence": 11302,
                "type": u"action",
                "updated_at": u"2017-06-19 08:15:22"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:15:22",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 111,
                "name_cn": u"新增",
                "name_en": u"post",
                "pid": 13,
                "sequence": 11303,
                "type": u"action",
                "updated_at": u"2017-06-19 08:15:22"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:15:22",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 112,
                "name_cn": u"删除",
                "name_en": u"delete",
                "pid": 13,
                "sequence": 11304,
                "type": u"action",
                "updated_at": u"2017-06-19 08:15:22"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:15:40",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 113,
                "name_cn": u"查看",
                "name_en": u"get",
                "pid": 14,
                "sequence": 11401,
                "type": u"action",
                "updated_at": u"2017-06-19 08:15:40"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:15:40",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 114,
                "name_cn": u"修改",
                "name_en": u"put",
                "pid": 14,
                "sequence": 11402,
                "type": u"action",
                "updated_at": u"2017-06-19 08:15:40"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:15:40",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 115,
                "name_cn": u"新增",
                "name_en": u"post",
                "pid": 14,
                "sequence": 11403,
                "type": u"action",
                "updated_at": u"2017-06-19 08:15:40"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:15:40",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 116,
                "name_cn": u"删除",
                "name_en": u"delete",
                "pid": 14,
                "sequence": 11404,
                "type": u"action",
                "updated_at": u"2017-06-19 08:15:40"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:16:21",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 117,
                "name_cn": u"查看",
                "name_en": u"get",
                "pid": 15,
                "sequence": 11501,
                "type": u"action",
                "updated_at": u"2017-06-19 08:16:21"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:16:21",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 118,
                "name_cn": u"修改",
                "name_en": u"put",
                "pid": 15,
                "sequence": 11502,
                "type": u"action",
                "updated_at": u"2017-06-19 08:16:21"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:16:21",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 119,
                "name_cn": u"新增",
                "name_en": u"post",
                "pid": 15,
                "sequence": 11503,
                "type": u"action",
                "updated_at": u"2017-06-19 08:16:21"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:16:21",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 120,
                "name_cn": u"删除",
                "name_en": u"delete",
                "pid": 15,
                "sequence": 11504,
                "type": u"action",
                "updated_at": u"2017-06-19 08:16:21"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:16:42",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 121,
                "name_cn": u"查看",
                "name_en": u"get",
                "pid": 16,
                "sequence": 11601,
                "type": u"action",
                "updated_at": u"2017-06-19 08:16:42"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:16:42",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 122,
                "name_cn": u"修改",
                "name_en": u"put",
                "pid": 16,
                "sequence": 11602,
                "type": u"action",
                "updated_at": u"2017-06-19 08:16:42"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:16:42",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 123,
                "name_cn": u"新增",
                "name_en": u"post",
                "pid": 16,
                "sequence": 11603,
                "type": u"action",
                "updated_at": u"2017-06-19 08:16:42"
            },
            {
                "archive": 0,
                "created_at": u"2017-06-19 08:16:42",
                "url": u"xx.yy.zz",
                "visible": 1,
                "icon": u"leaf",
                "id": 124,
                "name_cn": u"删除",
                "name_en": u"delete",
                "pid": 16,
                "sequence": 11604,
                "type": u"action",
                "updated_at": u"2017-06-19 08:16:42"
            }
        ]
        for asscess_data in access_list:
            access = MenuModel(
                    id=asscess_data['id'],
                    name_cn=asscess_data['name_cn'],
                    name_en=asscess_data['name_en'],
                    pid=asscess_data['pid'],
                    type=asscess_data['type'],
                    sequence=asscess_data['sequence'],
                    archive=asscess_data['archive'],
                    icon=asscess_data['icon'],
                    url=asscess_data['url'],
                    visible=asscess_data['visible']
            )
            access.save()


#: 1 创建 super, owner
class TestUser:
    user_super_login = deepcopy(user_super)
    user_owner_login = deepcopy(user_owner)

    def test_add_super(self):
        self.user_super_login['role'] = SUPER
        self.user_super_login['password'] = generate_password_hash(self.user_super_login['password'])
        user = UserModel(**self.user_super_login)
        user.save()

    def test_add_owner(self):
        self.user_owner_login['role'] = OWNER
        self.user_owner_login['password'] = generate_password_hash(self.user_owner_login['password'])
        user = UserModel(**self.user_owner_login)
        user.save()


#: 2 登录 super
@pytest.mark.usefixtures('db')
class TestApiPassport:
    """api role testing"""
    uri_prefix = '/api/passport'

    user_id = {}

    user_data = deepcopy(user_super)

    def test_base_fetch(self):
        u = UserModel.get_by_id(1)

    def test_login_super(self, user, testapp, client, db):
        """create successful."""

        resp = client.post('%s/login' % (self.uri_prefix), data=self.user_data)

        response_success(resp)

        del self.user_data['password']
        compare_req_resp(self.user_data, resp)


#: 3 创建 space, users
@pytest.mark.usefixtures('db')
class TestApiSpaceInit:
    """api role testing"""
    uri_prefix = '/api/space'

    user_id = {}

    #: user list (1, 2, 3)
    space_data = {
        'name': u'walle-web 2.0',
        'user_id': 2,
    }

    def test_base_create_space(self, user, testapp, client, db):
        """create successful."""
        # 1.create project
        resp = client.post('%s/' % (self.uri_prefix), data=self.space_data)

        response_success(resp)
        # compare_req_resp(self.space_data, resp)
        current_app.logger.info(resp_json(resp)['data'])
        self.space_data['space_id'] = resp_json(resp)['data']['id']
