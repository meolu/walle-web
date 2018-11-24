# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

import json

from flask import request, current_app
from flask_login import login_required
from walle.api.api import SecurityResource
from walle.form.project import ProjectForm
from walle.model.project import ProjectModel
from walle.model.user import MemberModel
from walle.service.rbac.role import *
from walle.service.extensions import permission


class ProjectAPI(SecurityResource):

    @permission.gte_develop_or_uid
    def get(self, action=None, project_id=None):
        """
        fetch project list or one item
        /project/<int:project_id>

        :return:
        """
        super(ProjectAPI, self).get()

        return self.item(project_id) if project_id else self.list()

    def list(self):
        """
        fetch project list

        :return:
        """
        page = int(request.args.get('page', 0))
        page = page - 1 if page else 0
        size = float(request.args.get('size', 10))
        kw = request.values.get('kw', '')
        environment_id = request.values.get('environment_id', '')

        project_model = ProjectModel()
        space_id = None if current_user.role == SUPER else session['space_id']
        project_list, count = project_model.list(page=page, size=size, kw=kw, environment_id=environment_id, space_id=space_id)
        return self.list_json(list=project_list, count=count, enable_create=permission.enable_role(MASTER))

    def item(self, project_id):
        """
        获取某个用户组

        :param id:
        :return:
        """

        project_model = ProjectModel(id=project_id)
        project_info = project_model.item()
        if not project_info:
            return self.render_json(code=-1)

        group_info = MemberModel().members(project_id=project_id)
        current_app.logger.info(group_info)

        return self.render_json(data=dict(project_info, **group_info))

    def post(self):
        """
        create a project
        /environment/

        :return:
        """
        super(ProjectAPI, self).post()

        form = ProjectForm(request.form, csrf_enabled=False)
        if form.validate_on_submit():
            project_new = ProjectModel()
            data = form.form2dict()
            id = project_new.add(data)
            if not id:
                return self.render_json(code=-1)

            return self.render_json(data=project_new.item())
        else:
            return self.render_json(code=-1, message=form.errors)

    def put(self, project_id, action=None):
        """
        update environment
        /environment/<int:id>

        :return:
        """
        super(ProjectAPI, self).put()

        if action and action == 'members':
            return self.members(project_id, members=json.loads(request.data))

        form = ProjectForm(request.form, csrf_enabled=False)
        form.set_id(project_id)
        if form.validate_on_submit():
            server = ProjectModel().get_by_id(project_id)
            data = form.form2dict()
            # a new type to update a model
            ret = server.update(data)
            return self.render_json(data=server.item())
        else:
            return self.render_json(code=-1, message=form.errors)

    def delete(self, project_id):
        """
        remove an environment
        /environment/<int:id>

        :return:
        """
        super(ProjectAPI, self).delete()

        project_model = ProjectModel(id=project_id)
        project_model.remove(project_id)

        return self.render_json(message='')

    def members(self, project_id, members):
        """

        :param project_id:
        :param members:
        :return:
        """
        # TODO login for group id
        group_id = 1

        group_model = MemberModel(project_id=project_id)
        ret = group_model.update_project(project_id=project_id, members=members)

        item = group_model.members()

        return self.render_json(data=item)

