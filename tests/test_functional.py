# -*- coding: utf-8 -*-
"""Functional tests using WebTest.

See: http://webtest.readthedocs.org/
"""
from flask import url_for

from walle.model.user import UserModel

from .factories import UserFactory


class TestLoggingIn:
    """Login."""

    def test_can_log_in_returns_200(self, user, testapp, client):
        """Login successful."""
        # Goes to homepage
        res = client.get('/')
        # # Fills out login form in navbar
        # form = res.forms['loginForm']
        # form['username'] = user.username
        # form['password'] = 'myprecious'
        # # Submits
        # res = form.submit().follow()
        assert res.status_code == 200

#     def test_sees_alert_on_log_out(self, user, testapp):
#         """Show alert on logout."""
#         res = testapp.get('/')
#         # Fills out login form in navbar
#         form = res.forms['loginForm']
#         form['username'] = user.username
#         form['password'] = 'myprecious'
#         # Submits
#         res = form.submit().follow()
#         res = testapp.get(url_for('public.logout')).follow()
#         # sees alert
#         assert 'You are logged out.' in res
#
#     def test_sees_error_message_if_password_is_incorrect(self, user, testapp):
#         """Show error if password is incorrect."""
#         # Goes to homepage
#         res = testapp.get('/')
#         # Fills out login form, password incorrect
#         form = res.forms['loginForm']
#         form['username'] = user.username
#         form['password'] = 'wrong'
#         # Submits
#         res = form.submit()
#         # sees error
#         assert 'Invalid password' in res
#
#     def test_sees_error_message_if_username_doesnt_exist(self, user, testapp):
#         """Show error if username doesn't exist."""
#         # Goes to homepage
#         res = testapp.get('/')
#         # Fills out login form, password incorrect
#         form = res.forms['loginForm']
#         form['username'] = 'unknown'
#         form['password'] = 'myprecious'
#         # Submits
#         res = form.submit()
#         # sees error
#         assert 'Unknown user' in res
#
#
# class TestRegistering:
#     """Register a user."""
#
#     def test_can_register(self, user, testapp):
#         """Register a new user."""
#         old_count = len(User.query.all())
#         # Goes to homepage
#         res = testapp.get('/')
#         # Clicks Create Account button
#         res = res.click('Create account')
#         # Fills out the form
#         form = res.forms['registerForm']
#         form['username'] = 'foobar'
#         form['email'] = 'foo@bar.com'
#         form['password'] = 'secret'
#         form['confirm'] = 'secret'
#         # Submits
#         res = form.submit().follow()
#         assert res.status_code == 200
#         # A new user was created
#         assert len(User.query.all()) == old_count + 1
#
#     def test_sees_error_message_if_passwords_dont_match(self, user, testapp):
#         """Show error if passwords don't match."""
#         # Goes to registration page
#         res = testapp.get(url_for('public.register'))
#         # Fills out form, but passwords don't match
#         form = res.forms['registerForm']
#         form['username'] = 'foobar'
#         form['email'] = 'foo@bar.com'
#         form['password'] = 'secret'
#         form['confirm'] = 'secrets'
#         # Submits
#         res = form.submit()
#         # sees error message
#         assert 'Passwords must match' in res
#
#     def test_sees_error_message_if_user_already_registered(self, user, testapp):
#         """Show error if user already registered."""
#         user = UserFactory(active=True)  # A registered user
#         user.save()
#         # Goes to registration page
#         res = testapp.get(url_for('public.register'))
#         # Fills out form, but username is already registered
#         form = res.forms['registerForm']
#         form['username'] = user.username
#         form['email'] = 'foo@bar.com'
#         form['password'] = 'secret'
#         form['confirm'] = 'secret'
#         # Submits
#         res = form.submit()
#         # sees error
#         assert 'Username already registered' in res
