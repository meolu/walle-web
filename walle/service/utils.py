# -*- coding: utf-8 -*-
"""Helper utilities and decorators."""
from __future__ import print_function

import sys
import time
from datetime import datetime

import os
import re
from flask import flash
from invoke import Responder


def flash_errors(form, category='warning'):
    """Flash all errors for a form."""
    for field, errors in form.errors.items():
        for error in errors:
            flash('{0} - {1}'.format(getattr(form, field).label.text, error), category)


def date_str_to_obj(ymd):
    return time.strptime(ymd, '%Y-%m-%d')


def datetime_str_to_obj(ymd_his):
    return datetime.strptime(ymd_his, "%Y-%m-%d %H:%i:%s")


PY2 = int(sys.version[0]) == 2

if PY2:
    text_type = unicode  # noqa
    binary_type = str
    string_types = (str, unicode)  # noqa
    unicode = unicode  # noqa
    basestring = basestring  # noqa
    reload(sys)
    sys.setdefaultencoding('utf8')
else:
    text_type = str
    binary_type = bytes
    string_types = (str,)
    unicode = str
    basestring = (str, bytes)


def detailtrace():
    from flask import current_app
    retStr = ""
    f = sys._getframe()
    f = f.f_back
    while hasattr(f, "f_code"):
        co = f.f_code
        retStr = "->%s(%s:%s)\n" % (os.path.basename(co.co_filename),
                                    co.co_name,
                                    f.f_lineno) + retStr
        f = f.f_back
    current_app.logger.info(retStr)
    print(retStr)


def color_clean(text_with_color):
    '''
    e.g \x1b[?1h\x1b=
    e.g \x1b[?1l\x1b>
    @param text_with_color:
    @return:
    '''
    pure_text = text_with_color.strip()
    pure_text = re.sub('\x1B\[[0-9;]*[mGK]', '', pure_text, flags=re.I)
    pure_text = re.sub('\x1B\[\?[0-9;]*[a-z]\x1B[=><]', '', pure_text, flags=re.I)
    return pure_text.strip()


def say_yes():
    return Responder(
        pattern=r'yes/no',
        response='yes\n',
    )
