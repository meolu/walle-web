"""add index

Revision ID: 52a2df18b1d4
Revises: 2bca06a823a0
Create Date: 2018-12-14 17:39:48.110670

"""
from alembic import op

revision = '52a2df18b1d4'
down_revision = '2bca06a823a0'
branch_labels = None
depends_on = None


def upgrade():
    op.create_index('idx_spaceId', 'environments', ['space_id'], unique=False)

    op.create_index('idx_spaceId', 'projects', ['space_id', 'name'], unique=False)

    op.create_index('idx_taskId', 'records', ['task_id'], unique=False)

    op.create_index('idx_name', 'servers', ['name'], unique=False)
    op.create_index('idx_username', 'users', ['username'], unique=True)

    op.create_index('idx_projectId', 'tasks', ['project_id', 'user_id'], unique=False)
    op.create_index('idx_userId', 'tasks', ['user_id', 'project_id'], unique=False)
    op.create_index('idx_name', 'tasks', ['name'], unique=False)

    op.create_index('idx_name', 'users', ['username', 'email'], unique=False)
    op.create_index('idx_user_source', 'members', ['source_type', 'source_id', 'access_level'], unique=False)


def downgrade():
    op.drop_index('idx_spaceId', table_name='environments')

    op.drop_index('idx_spaceId', table_name='projects')

    op.drop_index('idx_taskId', table_name='records')

    op.drop_index('idx_name', table_name='servers')
    op.drop_index('idx_username', table_name='users')

    op.drop_index('idx_projectId', table_name='tasks')
    op.drop_index('idx_userId', table_name='tasks')
    op.drop_index('idx_name', table_name='tasks')

    op.drop_index('idx_name', table_name='users')

    op.drop_index('idx_user_source', table_name='members')
