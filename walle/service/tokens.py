# -*- coding: utf-8 -*-
""" This file contains functions to generate and verify tokens for Flask-User.
    Tokens contain an encoded user ID and a signature. The signature is managed by the itsdangerous module.

    :copyright: (c) 2013 by Ling Thio
    :author: Ling Thio (ling.thio@gmail.com)
    :license: Simplified BSD License, see LICENSE.txt for more details."""

import base64
import hashlib

from Crypto.Cipher import AES
from Crypto.Random import get_random_bytes
from flask import current_app
from itsdangerous import BadSignature, SignatureExpired, TimestampSigner

from walle.config.settings import Config


class TokenManager(object):
    """Encrypt user IDs and sign the resulting tokens."""

    def __init__(self):
        """Create a key and signer from app configuration."""
        secret = Config.validate_secret_key(
            current_app.config.get('SECRET_KEY'))
        self.key = hashlib.sha256(secret.encode('utf-8')).digest()

        # Create signer to sign tokens
        self.signer = TimestampSigner(secret)

    def encrypt_id(self, id):
        """Encrypt an integer ID as URL-safe base64."""
        str1 = '%016d' % id
        iv = get_random_bytes(AES.block_size)
        cipher = AES.new(self.key, AES.MODE_CBC, iv)
        str2 = cipher.encrypt(str1.encode())
        return base64.urlsafe_b64encode(iv + str2).rstrip(b'=')

    def decrypt_id(self, encrypted_id):
        """Decrypt a URL-safe base64 encoded user ID."""
        if hasattr(encrypted_id, 'encode'):
            encrypted_id = encrypted_id.encode('ascii', 'ignore')

        try:
            padding = b'=' * (-len(encrypted_id) % 4)
            encrypted_data = base64.urlsafe_b64decode(encrypted_id + padding)
            iv = encrypted_data[:AES.block_size]
            ciphertext = encrypted_data[AES.block_size:]
            if not ciphertext or len(ciphertext) % AES.block_size:
                return 0
            cipher = AES.new(self.key, AES.MODE_CBC, iv)
            str1 = cipher.decrypt(ciphertext)
            return int(str1)
        except Exception:  # pragma: no cover
            return 0

    def generate_token(self, id):
        """Return a signed token for a user ID."""
        return self.signer.sign(self.encrypt_id(id)).decode()

    def verify_token(self, token, expiration_in_seconds):
        """Return validity, expiration, and user ID for a token."""
        try:
            data = self.signer.unsign(token, max_age=expiration_in_seconds)
            is_valid = True
            has_expired = False
            id = self.decrypt_id(data)
        except SignatureExpired:
            is_valid = False
            has_expired = True
            id = None
        except BadSignature:
            is_valid = False
            has_expired = False
            id = None
        return (is_valid, has_expired, id)
