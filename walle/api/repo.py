# -*- coding: utf-8 -*-
"""

    walle-web

    :copyright: © 2015-2017 walle-web.io
    :created time: 2017-03-25 11:15:01
    :author: wushuiyong@walle-web.io
"""
from flask import request, abort
from walle.api.api import SecurityResource
from walle.service.deployer import Deployer
from walle.service.extensions import permission


class RepoAPI(SecurityResource):
    actions = ['tags', 'branches', 'commits']

    @permission.upper_reporter
    def get(self, action, commit=None):
        """
        fetch project list or one item
        /project/<int:project_id>

        :return:
        """
        super(RepoAPI, self).get()
        project_id = request.args.get('project_id', '')

        if action in self.actions:
            self_action = getattr(self, action.lower(), None)
            return self_action(project_id=project_id)
        else:
            abort(404)


    def tags(self, project_id=None):
        """
        fetch project list or one item
        /tag/

        :return:
        """
        wi = Deployer(project_id=project_id)
        tag_list = wi.list_tag()
        tags = tag_list.stdout.strip().split('\n')
        return self.render_json(data={
            'tags': tags,
        })

    def branches(self, project_id=None):
        """
        fetch project list or one item
        /tag/

        :return:
        """
        wi = Deployer(project_id=project_id)
        branches = wi.list_branch()
        return self.render_json(data={
            'branches': branches,
        })

    def commits(self, project_id):
        """
        fetch project list or one item
        /tag/

        :return:
        """
        branch = request.args.get('branch', '')
        wi = Deployer(project_id=project_id)
        commits = wi.list_commit(branch)

        return self.render_json(data={
            'branches': commits,
        })
