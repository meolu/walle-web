# -*- coding: utf-8 -*-
"""Test configs."""
from walle.app import create_app
from walle.config.settings_dev import DevConfig
from walle.config.settings_test import TestConfig
from walle.config.settings_prod import ProdConfig


# def test_production_config():
#     """Production config."""
#     app = create_app(ProdConfig)
#     assert app.config['ENV'] == 'prod'
#     assert app.config['DEBUG'] is False
#     assert app.config['DEBUG_TB_ENABLED'] is False
#     assert app.config['ASSETS_DEBUG'] is False


# def test_dev_config():
#     """Development config."""
#     app = create_app(DevConfig)
#     assert app.config['ENV'] == 'dev'
#     assert app.config['DEBUG'] is True
#     assert app.config['ASSETS_DEBUG'] is True

def test_test_config():
    """Development config."""
    app = create_app(TestConfig)
    assert app.config['TESTING'] == True
    assert app.config['DEBUG'] is True
