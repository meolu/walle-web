""" This file contains email sending functions for Flask-User.
    It uses Jinja2 to render email subject and email message. It uses Flask-Mail to send email.

    :copyright: (c) 2013 by Ling Thio
    :author: Ling Thio (ling.thio@gmail.com)
    :license: Simplified BSD License, see LICENSE.txt for more details."""

import smtplib
import socket

from flask import current_app, render_template
from flask import url_for

from walle.service.extensions import mail
from flask_mail import Message
from walle.service import tokens


def _render_email(filename, **kwargs):
    # Render subject
    subject = render_template(filename + '_subject.txt', **kwargs)
    # Make sure that subject lines do not contain newlines
    subject = subject.replace('\n', ' ')
    subject = subject.replace('\r', ' ')
    # Render HTML message
    html_message = render_template(filename + '_message.html', **kwargs)
    # Render text message
    text_message = render_template(filename + '_message.txt', **kwargs)

    return (subject, html_message, text_message)


def send_email(recipient, subject, html_message, text_message):
    """ Send email from default sender to 'recipient' """
    # Make sure that Flask-Mail has been initialized
    mail_engine = mail
    if not mail_engine:
        return 'Flask-Mail has not been initialized. Initialize Flask-Mail or disable USER_SEND_PASSWORD_CHANGED_EMAIL, USER_SEND_REGISTERED_EMAIL and USER_SEND_USERNAME_CHANGED_EMAIL'

    try:

        # Construct Flash-Mail message
        message = Message(subject,
                          recipients=[recipient],
                          html=html_message,
                          body=text_message)
        return mail.send(message)

    # Print helpful error messages on exceptions
    except (socket.gaierror, socket.error) as e:
        return 'SMTP Connection error: Check your MAIL_SERVER and MAIL_PORT settings.'
    except smtplib.SMTPAuthenticationError:
        return 'SMTP Authentication error: Check your MAIL_USERNAME and MAIL_PASSWORD settings.'


def get_primary_user_email(user):
    user_manager = current_app.user_manager
    db_adapter = user_manager.db_adapter
    if db_adapter.UserEmailClass:
        user_email = db_adapter.find_first_object(db_adapter.UserEmailClass,
                                                  user_id=int(user.get_id()),
                                                  is_primary=True)
        return user_email
    else:
        return user


def send_confirm_email_email(user, user_email, confirm_email_link):
    # Verify certain conditions
    user_manager = current_app.user_manager
    if not user_manager.enable_email: return
    if not user_manager.send_registered_email and not user_manager.enable_confirm_email: return

    # Retrieve email address from User or UserEmail object
    email = user_email.email if user_email else user.email
    assert (email)

    # Render subject, html message and text message
    subject, html_message, text_message = _render_email(
            user_manager.confirm_email_email_template,
            user=user,
            app_name=user_manager.app_name,
            confirm_email_link=confirm_email_link)

    # Send email message using Flask-Mail
    user_manager.send_email_function(email, subject, html_message, text_message)


def send_registered_email(user, confirm_email_link):  # pragma: no cover
    # Verify certain conditions
    # user_manager =  current_app.user_manager
    # if not user_manager.enable_email: return
    # if not user_manager.send_registered_email: return

    # Retrieve email address from User or UserEmail object
    email = user.email
    assert (email)

    # Render subject, html message and text message
    subject, html_message, text_message = _render_email(
            'emails/registered',
            user=user,
            app_name='walle',
            confirm_email_link=confirm_email_link)

    # Send email message using Flask-Mail
    return send_email(email, subject, html_message, text_message)


def public_send_registered_email(user, require_email_confirmation=True):
    # Send 'confirm_email' or 'registered' email
    # Generate confirm email link
    token_manager = tokens.TokenManager()
    token = token_manager.generate_token(int(user.id))
    confirm_email_link = url_for('deploy.confirm_mail', token=token, _external=True)

    # Send email
    return send_registered_email(user, confirm_email_link)
