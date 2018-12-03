# -*- coding: utf-8 -*-
"""Helper utilities and decorators."""
import sys
import time
from datetime import datetime

import os
from flask import flash


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
    print retStr
