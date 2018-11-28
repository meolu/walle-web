# -*- coding: utf-8 -*-
"""Factories to help in tests."""
from factory import Sequence
from factory.alchemy import SQLAlchemyModelFactory
from walle.model.database import db
from walle.model.user import UserModel
from werkzeug.security import generate_password_hash


class BaseFactory(SQLAlchemyModelFactory):
    """Base factory."""

    class Meta:
        """Factory configuration."""

        abstract = True
        sqlalchemy_session = db.session


class UserFactory(BaseFactory):
    """User factory."""

    username = Sequence(lambda n: 'test{0}'.format(n))
    email = Sequence(lambda n: 'test{0}@walle.com'.format(n))
    password = generate_password_hash('test0pwd')

    class Meta:
        """Factory configuration."""

        model = UserModel


import pytest


@pytest.mark.usefixtures('db')
class TestApiBase:

    def init_vars(self, data):
        from flask_login import current_user
        if 'space_id' in data:
            data['space_id'] = current_user.space_id()
        if 'user_id' in data:
            data['user_id'] = current_user.id

