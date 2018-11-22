# -*- coding: utf-8 -*-
"""Create an application instance."""
import sys

from flask.helpers import get_debug_flag
from walle.app import create_app
from walle.config.settings_dev import DevConfig
from walle.config.settings_test import TestConfig
from walle.config.settings_prod import ProdConfig

CONFIG = DevConfig if get_debug_flag() else ProdConfig

if len(sys.argv) > 2 and sys.argv[2] == 'test':
    CONFIG = TestConfig

app = create_app(CONFIG)
