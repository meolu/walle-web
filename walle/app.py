# -*- coding: utf-8 -*-
"""The app module, containing the app factory function."""
import logging
import sys, os

from flask import Flask, render_template, current_app, session,request, abort, Response
from flask_restful import Api
from tornado.ioloop import IOLoop
from tornado.web import Application, FallbackHandler
from tornado.wsgi import WSGIContainer
from walle import commands
from walle.api.api import ApiResource
from walle.api import access as AccessAPI
from walle.api import api as BaseAPI
from walle.api import deploy as DeployAPI
from walle.api import environment as EnvironmentAPI
from walle.api import group as GroupAPI
from walle.api import passport as PassportAPI
from walle.api import project as ProjectAPI
from walle.api import general as GeneralAPI
from walle.api import role as RoleAPI
from walle.api import server as ServerAPI
from walle.api import task as TaskAPI
from walle.api import user as UserAPI
from walle.api import space as SpaceAPI
from walle.api import repo as RepoApi
from walle.config.settings_dev import DevConfig
from walle.config.settings_test import TestConfig
from walle.config.settings_prod import ProdConfig
from walle.model.user import UserModel, MemberModel
from walle.service.extensions import bcrypt, csrf_protect, db, migrate
from walle.service.extensions import login_manager, mail, permission, socketio
from walle.service.error import WalleError
from walle.service.websocket import WSHandler
from flask_socketio import emit, join_room, leave_room

from walle.service.code import Code
from flask_login import current_user


# TODO 添加到这,则对单测有影响
# app = Flask(__name__.split('.')[0])

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
        # current_app.logger.info(request)
        # current_app.logger.info(app.request_class.url_rule)
        # TODO
        app.logger.info('============ @app.before_request ============')

    @app.teardown_request
    def shutdown_session(exception=None):
        # TODO
        from walle.model.database import db
        db.session.remove()
        current_app.logger.info('============ @app.teardown_request ============')

    @app.route('/api/websocket')
    def index():
        return render_template('socketio.html')

    # @app.route('/api/socketio')
    # def index():
    #
    #     return render_template('socketio.html')

    # 测试环境跑单测失败
    # if not app.config['TESTING']:
    #     register_websocket(app)

    register_socketio(app)

    reload(sys)
    sys.setdefaultencoding('utf-8')

    return app


def register_extensions(app):
    """Register Flask extensions."""
    bcrypt.init_app(app)
    db.init_app(app)
    csrf_protect.init_app(app)
    login_manager.session_protection = 'strong'

    @login_manager.user_loader
    def load_user(user_id):
        current_app.logger.info(user_id)
        app.logger.info('============ @app.user_loader ============')

        return UserModel.query.get(user_id)


    @login_manager.unauthorized_handler
    def unauthorized():
        # TODO log
        current_app.logger.info('============ @login_manager.unauthorized_handler ============')
        # return Response(ApiResource.render_json(code=Code.space_error))
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
    api.add_resource(EnvironmentAPI.EnvironmentAPI, '/api/environment/', '/api/environment/<int:env_id>',
                     endpoint='environment')

    return None


def register_errorhandlers(app):
    """Register error handlers."""
    @app.errorhandler(WalleError)
    def render_error(error):
        app.logger.info('============ register_errorhandlers ============')
        # response 的 json 内容为自定义错误代码和错误信息
        return error.render_error()

    def render_errors():

        """Render error template."""
        app.logger.info('============ render_errors ============')
        # If a HTTPException, pull the `code` attribute; default to 500
        return ApiResource.render_json(code=Code.space_error)
    #
    #     error_code = getattr(error, 'code', 500)
    #     return render_template('{0}.html'.format(error_code)), error_code


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
    socketio.init_app(app)
    namespace = '/walle'
    room = 12

    @socketio.on('open', namespace=namespace)
    def joined(message):
        app.logger.info('====== join =====')
        app.logger.info(message)

        if not current_user.is_authenticated:
            emit('close', {'msg': 'closing becuse you are not login'})

        join_room(room=room, namespace=namespace)
        emit('console', {'msg': 'opening....'}, room=room)

    @socketio.on('deploy', namespace=namespace)
    def test_message(message):
        # join_room(room="BigData", sid=1, namespace=namespace)
        emit('console', {'msg': message['msg']}, room=room)
        from walle.service.deployer import DeploySocketIO
        wi = DeploySocketIO(12)
        ret = wi.deploy()

    socketio.run(app, host=app.config.get('HOST'), port=app.config.get('PORT'))
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

