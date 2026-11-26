# -*- coding: utf-8 -*-
"""Space-scoped API access tests."""

from walle.model.environment import EnvironmentModel
from walle.model.member import MemberModel
from walle.model.project import ProjectModel
from walle.model.server import ServerModel
from walle.model.space import SpaceModel
from walle.model.task import TaskModel
from walle.model.user import UserModel


def test_resource_ids_are_scoped_to_active_space(client, db):
    """Reject cross-space resource IDs while preserving same-space access."""
    user = UserModel(
        username='scope-user', email='scope-user@example.com',
        password='x', role='MASTER')
    other_user = UserModel(
        username='scope-other', email='scope-other@example.com',
        password='x', role='MASTER')
    db.session.add_all([user, other_user])
    db.session.commit()

    space_one = SpaceModel(user_id=user.id, name='space-one', status=1)
    space_two = SpaceModel(user_id=other_user.id, name='space-two', status=1)
    db.session.add_all([space_one, space_two])
    db.session.commit()

    for space, member in [(space_one, user), (space_two, other_user)]:
        db.session.add(MemberModel(
            user_id=member.id, source_id=space.id, source_type='group',
            access_level='MASTER', status=1))

    environment = EnvironmentModel(
        name='private-env', space_id=space_two.id, status=1)
    own_environment = EnvironmentModel(
        name='own-env', space_id=space_one.id, status=1)
    project = ProjectModel(
        name='private-project', user_id=other_user.id, environment_id=1,
        space_id=space_two.id, status=1, keep_version_num=3, server_ids='',
        repo_url='https://example.com/private.git',
        repo_password='private-secret',
        repo_mode='branch', target_root='/tmp/root',
        target_releases='/tmp/releases')
    db.session.add_all([environment, own_environment, project])
    db.session.commit()

    server = ServerModel(
        name='private-server', host='192.0.2.1', user='deploy', port=22,
        status=1)
    db.session.add(server)
    db.session.commit()
    project.server_ids = str(server.id)
    task = TaskModel(
        name='private-task', user_id=other_user.id, user_name='other',
        project_id=project.id, status=TaskModel.status_new,
        servers=str(server.id), keep_version_num=3)
    db.session.add(task)
    db.session.commit()
    project_id = project.id
    environment_id = environment.id
    own_environment_id = own_environment.id
    server_id = server.id
    task_id = task.id
    space_one_id = space_one.id
    space_two_id = space_two.id
    user_id = user.id
    user.last_space = space_one_id
    user.password = user.get_password('scope-password')
    db.session.commit()
    login = client.post('/api/passport/login', data={
        'email': 'scope-user@example.com', 'password': 'scope-password'})
    assert login.json['code'] == 0

    project_url = '/api/project/%d' % project_id
    project_data = {
        'name': 'changed-project', 'environment_id': environment_id,
        'space_id': space_two_id, 'server_ids': str(server_id),
        'target_root': '/tmp/root', 'target_releases': '/tmp/releases',
        'repo_url': 'https://example.com/changed.git', 'repo_mode': 'branch'}
    project_get = client.get(project_url)
    project_put = client.put(project_url, data=project_data)
    project_delete = client.delete(project_url)

    assert [project_get.json['code'], project_put.json['code'],
            project_delete.json['code']] == [-1, -1, -1]
    assert 'private-secret' not in project_get.get_data(as_text=True)
    project = ProjectModel.query.get(project_id)
    assert project.name == 'private-project'
    assert project.status == 1

    env_url = '/api/environment/%d' % environment_id
    env_get = client.get(env_url)
    env_put = client.put(env_url, data={
        'env_name': 'changed-env', 'status': '1'})
    env_delete = client.delete(env_url)
    assert [env_get.json['code'], env_put.json['code'],
            env_delete.json['code']] == [-1, -1, -1]
    environment = EnvironmentModel.query.get(environment_id)
    assert environment.name == 'private-env'
    assert environment.status == 1

    server_url = '/api/server/%d' % server_id
    server_get = client.get(server_url)
    server_put = client.put(server_url, data={
        'name': 'changed-server', 'host': '192.0.2.2', 'user': 'deploy',
        'port': 22})
    server_delete = client.delete(server_url)
    assert [server_get.json['code'], server_put.json['code'],
            server_delete.json['code']] == [-1, -1, -1]
    server = ServerModel.query.get(server_id)
    assert server.name == 'private-server'
    assert server.status == 1

    task_url = '/api/task/%d' % task_id
    task_get = client.get(task_url)
    task_put = client.put(task_url, data={
        'name': 'changed-task', 'project_id': project_id,
        'servers': str(server_id)})
    task_delete = client.delete(task_url)
    assert task_get.json['code'] == -1
    assert task_put.json['code'] == -1
    assert task_delete.json['code'] == -1
    task = TaskModel.query.get(task_id)
    assert task.name == 'private-task'
    assert task.status == TaskModel.status_new

    deploy_get = client.get('/api/deploy/%d' % task_id)
    assert deploy_get.json['code'] == -1

    group_url = '/api/group/%d' % space_two_id
    group_get = client.get(group_url)
    group_put = client.put(group_url, data={'uid_roles': '[]'})
    group_delete = client.delete(group_url)
    assert [group_get.json['code'], group_put.json['code'],
            group_delete.json['code']] == [-1, -1, -1]
    group_members = MemberModel.query.filter_by(
        source_id=space_two_id, source_type='group').all()
    assert len(group_members) == 1

    repo_get = client.get(
        '/api/repo/tags/?project_id=%d' % project_id)
    assert repo_get.json['code'] == -1

    own_project = ProjectModel(
        name='own-project', user_id=user_id,
        environment_id=own_environment_id, space_id=space_one_id,
        status=1, keep_version_num=3, server_ids=str(server.id),
        repo_url='https://example.com/own.git',
        repo_password='own-secret', repo_mode='branch',
        target_root='/tmp/root',
        target_releases='/tmp/releases')
    db.session.add(own_project)
    db.session.commit()

    own_project_id = own_project.id
    own_url = '/api/project/%d' % own_project_id
    own_get = client.get(own_url)
    assert own_get.json['code'] == 0
    own_data = dict(
        project_data, name='updated-own-project',
        environment_id=own_environment_id, space_id=space_two_id)
    own_put = client.put(own_url, data=own_data)
    assert own_put.json['code'] == 0
    own_delete = client.delete(own_url)
    assert own_delete.json['code'] == 0
    own_project = ProjectModel.query.get(own_project_id)
    assert own_project.space_id == space_one_id
    assert own_project.status == ProjectModel.status_remove
