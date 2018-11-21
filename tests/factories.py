# -*- coding: utf-8 -*-
"""Factories to help in tests."""
from factory import PostGenerationMethodCall, Sequence
from factory.alchemy import SQLAlchemyModelFactory
from werkzeug.security import generate_password_hash

from walle.model.database import db
from walle.model.user import UserModel


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
