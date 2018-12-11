# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""

import json

from flask import request
from walle.api.api import SecurityResource
from walle.form.project import ProjectForm
from walle.model.member import MemberModel
from walle.model.project import ProjectModel
from walle.service.extensions import permission
from walle.service.rbac.role import *


class ProjectAPI(SecurityResource):

    @permission.upper_reporter
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
        size = int(request.args.get('size', 10))
        kw = request.values.get('kw', '')
        environment_id = request.values.get('environment_id', '')

        project_model = ProjectModel()
        project_list, count = project_model.list(page=page, size=size, kw=kw, environment_id=environment_id,
                                                 space_id=self.space_id)
        return self.list_json(list=project_list, count=count,
                              enable_create=permission.role_upper_master() and current_user.role != SUPER)

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

        project_info['members'], count, project_info['user_uids'] = MemberModel().members(project_id=project_id)

        return self.render_json(data=project_info)

    @permission.upper_developer
    def post(self):
        """
        create a project
        /environment/

        :return:
        """
        super(ProjectAPI, self).post()

        form = ProjectForm(request.form, csrf_enabled=False)
        if form.validate_on_submit():
            # add project
            project_new = ProjectModel()
            data = form.form2dict()
            id = project_new.add(data)
            # TODO
            if not id:
                return self.render_json(code=-1)

            return self.render_json(data=project_new.item())
        else:
            return self.render_error(code=Code.form_error, message=form.errors)

    @permission.upper_developer
    def put(self, project_id, action=None):
        """
        update project
        /project/<int:id>

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
            return self.render_error(code=Code.form_error, message=form.errors)

    @permission.upper_developer
    def delete(self, project_id):
        """
        remove an project
        /project/<int:id>

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

        group_model = MemberModel(project_id=project_id)
        ret = group_model.update_project(project_id=project_id, members=members)

        item, count, user_ids = group_model.members()

        return self.render_json(data=item)
