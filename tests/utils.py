# -*- coding: utf-8 -*-
"""
    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-05-20 22:25:27
    :author: wushuiyong@walle-web.io
"""
import json
import sys
from flask import current_app

PY2 = int(sys.version[0]) == 2

if PY2:
    from urllib import urlencode
else:
    from urllib.parse import urlencode


def response_success(response):
    assert 200 <= response.status_code < 300, 'Received %d response: %s' % (response.status_code, response.data)
    resp = resp_json(response)
    assert resp['code'] == 0, 'Received %d response: %s' % (resp['code'], response.data)


def response_error(response, code=None):
    assert 200 <= response.status_code < 300, 'Received %d response: %s' % (response.status_code, response.data)
    resp = resp_json(response)
    assert resp['code'] != 0, 'Received %d response: %s' % (resp['code'], response.data)


def compare_req_resp(req_obj, resp):
    resp_obj = resp_json(resp)['data']

    compare_in(req_obj, resp_obj)


def compare_in(req_obj, resp_obj):
    for k, v in req_obj.items():
        assert k in resp_obj.keys(), 'Key %r not in response (keys are %r)' % (k, resp_obj.keys())
        assert resp_obj[k] == v, 'Value for key %r should be %r but is %r' % (k, v, resp_obj[k])


def resp_json(resp):
    return json.loads(resp.get_data(as_text=True))
