# -*- coding: utf-8 -*-
"""The app module, containing the app factory function."""
import gevent.monkey
gevent.monkey.patch_all()

import logging
import sys
import os
from flask import Flask, render_template, current_app
from flask_restful import Api
from walle import commands
from walle.api import access as AccessAPI
from walle.api import api as BaseAPI
from walle.api import deploy as DeployAPI
from walle.api import environment as EnvironmentAPI
from walle.api import general as GeneralAPI
from walle.api import group as GroupAPI
from walle.api import passport as PassportAPI
from walle.api import project as ProjectAPI
from walle.api import repo as RepoApi
from walle.api import role as RoleAPI
from walle.api import server as ServerAPI
from walle.api import space as SpaceAPI
from walle.api import task as TaskAPI
from walle.api import user as UserAPI
from walle.config.settings_prod import ProdConfig
from walle.model.user import UserModel, AnonymousUser
from walle.service.code import Code
from walle.service.error import WalleError
from walle.service.extensions import bcrypt, csrf_protect, db, migrate
from walle.service.extensions import login_manager, mail, permission, socketio
from walle.service.websocket import WalleSocketIO


def create_app(config_object=ProdConfig):
    """An application factory, as explained here: http://flask.pocoo.org/docs/patterns/appfactories/.

    :param config_object: The configuration object to use.
    """
    app = Flask(__name__.split('.')[0])
    app.config.from_object(config_object)
    register_extensions(app)
    register_blueprints(app)
    register_errorhandlers(app)
    register_shellcontext(app)
    register_commands(app)
    register_logging(app)

    @app.before_request
    def before_request():
        # TODO
        pass

    @app.teardown_request
    def shutdown_session(exception=None):
        # TODO
        from walle.model.database import db
        db.session.remove()

    @app.route('/api/websocket')
    def index():
        return render_template('socketio.html')

    # 单元测试不用开启 websocket
    if app.config.get('ENV') != 'test':
        register_socketio(app)

    try:
        reload(sys)
        sys.setdefaultencoding('utf-8')
    except NameError:
        pass

    return app


def register_extensions(app):
    """Register Flask extensions."""
    bcrypt.init_app(app)
    db.init_app(app)
    csrf_protect.init_app(app)
    login_manager.session_protection = 'strong'
    login_manager.anonymous_user = AnonymousUser

    @login_manager.user_loader
    def load_user(user_id):
        current_app.logger.info(user_id)

        return UserModel.query.get(user_id)

    @login_manager.unauthorized_handler
    def unauthorized():
        # TODO log
        return BaseAPI.ApiResource.json(code=Code.unlogin)

    login_manager.init_app(app)

    migrate.init_app(app, db)
    mail.init_app(app)
    permission.init_app(app)

    return app


def register_blueprints(app):
    """Register Flask blueprints."""
    api = Api(app)
    api.add_resource(BaseAPI.Base, '/', endpoint='root')
    api.add_resource(GeneralAPI.GeneralAPI, '/api/general/<string:action>', endpoint='general')
    api.add_resource(SpaceAPI.SpaceAPI, '/api/space/', '/api/space/<int:space_id>', '/api/space/<int:space_id>/<string:action>', endpoint='space')
    api.add_resource(DeployAPI.DeployAPI, '/api/deploy/', '/api/deploy/<int:task_id>', endpoint='deploy')
    api.add_resource(AccessAPI.AccessAPI, '/api/access/', '/api/access/<int:access_id>', endpoint='access')
    api.add_resource(RoleAPI.RoleAPI, '/api/role/', endpoint='role')
    api.add_resource(GroupAPI.GroupAPI, '/api/group/', '/api/group/<int:group_id>', endpoint='group')
    api.add_resource(PassportAPI.PassportAPI, '/api/passport/', '/api/passport/<string:action>', endpoint='passport')
    api.add_resource(UserAPI.UserAPI, '/api/user/', '/api/user/<int:user_id>/<string:action>', '/api/user/<string:action>', '/api/user/<int:user_id>', endpoint='user')
    api.add_resource(ServerAPI.ServerAPI, '/api/server/', '/api/server/<int:id>', endpoint='server')
    api.add_resource(ProjectAPI.ProjectAPI, '/api/project/', '/api/project/<int:project_id>', '/api/project/<int:project_id>/<string:action>', endpoint='project')
    api.add_resource(RepoApi.RepoAPI, '/api/repo/<string:action>/', endpoint='repo')
    api.add_resource(TaskAPI.TaskAPI, '/api/task/', '/api/task/<int:task_id>', '/api/task/<int:task_id>/<string:action>', endpoint='task')
    api.add_resource(EnvironmentAPI.EnvironmentAPI, '/api/environment/', '/api/environment/<int:env_id>', endpoint='environment')

    return None


def register_errorhandlers(app):
    """Register error handlers."""

    @app.errorhandler(WalleError)
    def render_error(error):
        # response 的 json 内容为自定义错误代码和错误信息
        app.logger.error(error, exc_info=1)
        return error.render_error()


def register_shellcontext(app):
    """Register shell context objects."""

    def shell_context():
        """Shell context objects."""
        return {
            'db': db,
            'User': UserModel,
        }

    app.shell_context_processor(shell_context)


def register_commands(app):
    """Register Click commands."""
    app.cli.add_command(commands.test)
    app.cli.add_command(commands.lint)
    app.cli.add_command(commands.clean)
    app.cli.add_command(commands.urls)


def register_logging(app):
    # TODO https://blog.csdn.net/zwxiaoliu/article/details/80890136
    # email errors to the administrators
    import logging
    from logging.handlers import RotatingFileHandler
    # Formatter
    formatter = logging.Formatter(
            '%(asctime)s %(levelname)s %(pathname)s %(lineno)s %(module)s.%(funcName)s %(message)s')

    # log dir
    if not os.path.exists(app.config['LOG_PATH']):
        os.makedirs(app.config['LOG_PATH'])

    # FileHandler Info
    file_handler_info = RotatingFileHandler(filename=app.config['LOG_PATH_INFO'])
    file_handler_info.setFormatter(formatter)
    file_handler_info.setLevel(logging.INFO)
    info_filter = InfoFilter()
    file_handler_info.addFilter(info_filter)
    app.logger.addHandler(file_handler_info)

    # FileHandler Error
    file_handler_error = RotatingFileHandler(filename=app.config['LOG_PATH_ERROR'])
    file_handler_error.setFormatter(formatter)
    file_handler_error.setLevel(logging.ERROR)
    app.logger.addHandler(file_handler_error)


def register_socketio(app):
    if len(sys.argv) > 1 and sys.argv[1] == 'db':
        return app
    socketio.init_app(app, async_mode='gevent')
    socketio.on_namespace(WalleSocketIO(namespace='/walle'))
    socketio.run(app, debug=app.config.get('DEBUG'), host=app.config.get('HOST'), port=app.config.get('PORT'))
    return app


class InfoFilter(logging.Filter):
    def filter(self, record):
        """only use INFO
        筛选, 只需要 INFO 级别的log
        :param record:
        :return:
        """
        if logging.INFO <= record.levelno < logging.ERROR:
            # 已经是INFO级别了
            # 然后利用父类, 返回 1
            return 1
        else:
            return 0
