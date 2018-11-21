# -*- coding: utf-8 -*-
"""Defines fixtures available to all tests."""

import pytest

from walle.app import create_app
from walle.config.settings_test import TestConfig
from walle.model.database import db as _db
from webtest import TestApp
from .factories import UserFactory


@pytest.yield_fixture(scope='session')
def app():
    """An application for the tests."""
    _app = create_app(TestConfig)
    # _app.config['LOGIN_DISABLED'] = True
    _app.login_manager.init_app(_app)
    ctx = _app.test_request_context()
    ctx.push()

    yield _app

    ctx.pop()


@pytest.yield_fixture(scope='session')
def client(app):
    """A Flask test client. An instance of :class:`flask.testing.TestClient`
    by default.
    """
    with app.test_client() as client:
        yield client


@pytest.fixture(scope='session')
def testapp(app):
    """A Webtest app."""

    return TestApp(app)


@pytest.yield_fixture(scope='session')
def db(app):
    """A database for the tests."""
    _db.app = app
    with app.app_context():
        _db.create_all()

    yield _db

    # Explicitly close DB connection
    _db.session.close()
    _db.drop_all()


@pytest.fixture(scope='session')
def user(db):
    """A user for the tests."""
    user = UserFactory()
    db.session.commit()
    return user
