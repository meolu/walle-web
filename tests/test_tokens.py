"""Tests for signed user tokens."""

import pytest
from itsdangerous import TimestampSigner

from walle.app import create_app
from walle.service.tokens import TokenManager


def test_rejects_tokens_signed_with_legacy_secret(app):
    """Reject signatures created with the former literal secret."""
    manager = TokenManager()
    forged_token = TimestampSigner('SECRET_KEY').sign(
        manager.encrypt_id(7)).decode()

    assert manager.verify_token(forged_token, 86400) == (False, False, None)


@pytest.mark.parametrize(('flow', 'user_id'), [
    ('confirmation', 17),
    ('password reset', 23),
])
def test_configured_secret_round_trips_for_auth_flows(app, flow, user_id):
    """Round-trip configured-secret tokens used by auth email flows."""
    token = TokenManager().generate_token(user_id)

    assert TokenManager().verify_token(token, 86400) == (
        True, False, user_id), flow


@pytest.mark.parametrize(
    'secret', [None, 'secret-key', 'SECRET_KEY', 'too-short'])
def test_app_startup_rejects_missing_and_placeholder_secrets(secret):
    """Fail app creation when configuration contains an unsafe secret."""
    class InvalidConfig(object):
        SECRET_KEY = secret

    with pytest.raises(RuntimeError, match='WALLE_SECRET'):
        create_app(InvalidConfig)
