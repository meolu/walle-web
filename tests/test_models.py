# -*- coding: utf-8 -*-
"""Model unit tests."""

import pytest

from walle.model.environment import EnvironmentModel


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


class TestEnvironment:
    def test_add(self):
        env_new = EnvironmentModel()
        env_id = env_new.add(env_name=u'开发环境', space_id=1)

    # class TestUser:
    #     """User tests."""
    #
    #     def test_get_by_id(self):
    #         """Get user by ID."""
    #         user = Foo(username='wushuiyongoooo', email='wushuiyong@mail.com')
    #         user.save()
    #
    #         retrieved = User.get_by_id(user.id)
    #         assert retrieved == user

    # def test_created_at_defaults_to_datetime(self):
    #     """Test creation date."""
    #     user = User(username='foo', email='foo@bar.com')
    #     user.save()
    #     assert bool(user.created_at)
    #     assert isinstance(user.created_at, dt.datetime)
    #
    # def test_password_is_nullable(self):
    #     """Test null password."""
    #     user = User(username='foo', email='foo@bar.com')
    #     user.save()
    #     assert user.password is None
    #
    # def test_factory(self, db):
    #     """Test user factory."""
    #     user = UserFactory(password='myprecious')
    #     db.session.commit()
    #     assert bool(user.username)
    #     assert bool(user.email)
    #     assert bool(user.created_at)
    #     assert user.is_admin is False
    #     assert user.active is True
    #     assert user.check_password('myprecious')
    #
    # def test_check_password(self):
    #     """Check password."""
    #     user = User.create(username='foo', email='foo@bar.com',
    #                        password='foobarbaz123')
    #     assert user.check_password('foobarbaz123') is True
    #     assert user.check_password('barfoobaz') is False
    #
    # def test_full_name(self):
    #     """User full name."""
    #     user = UserFactory(first_name='Foo', last_name='Bar')
    #     assert user.full_name == 'Foo Bar'
    #
    # def test_roles(self):
    #     """Add a role to a user."""
    #     role = Role(name='admin')
    #     role.save()
    #     user = UserFactory()
    #     user.roles.append(role)
    #     user.save()
    #     assert role in user.roles
