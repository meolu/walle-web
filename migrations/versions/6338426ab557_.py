"""empty message

Revision ID: 6338426ab557
Revises: 
Create Date: 2017-05-18 14:43:27.361766

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = '6338426ab557'
down_revision = None
branch_labels = None
depends_on = None


def upgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.create_table('environment',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('name', sa.String(length=20), nullable=True),
    sa.Column('status', sa.Integer(), nullable=True),
    sa.PrimaryKeyConstraint('id')
    )
    op.create_table('foo',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('username', sa.String(length=50), nullable=False),
    sa.Column('email', sa.String(length=100), nullable=True),
    sa.Column('inserted_at', sa.DateTime(), nullable=True),
    sa.Column('created_at', sa.DateTime(), nullable=True),
    sa.Column('updated_at', sa.DateTime(), nullable=True),
    sa.PrimaryKeyConstraint('id'),
    sa.UniqueConstraint('email'),
    sa.UniqueConstraint('username')
    )
    op.create_table('project',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('user_id', sa.Integer(), nullable=True),
    sa.Column('name', sa.String(length=100), nullable=True),
    sa.Column('environment_id', sa.Integer(), nullable=True),
    sa.Column('status', sa.Integer(), nullable=True),
    sa.Column('version', sa.String(length=40), nullable=True),
    sa.Column('excludes', sa.Text(), nullable=True),
    sa.Column('target_user', sa.String(length=50), nullable=True),
    sa.Column('target_root', sa.String(length=200), nullable=True),
    sa.Column('target_library', sa.String(length=200), nullable=True),
    sa.Column('servers', sa.Text(), nullable=True),
    sa.Column('prev_deploy', sa.Text(), nullable=True),
    sa.Column('post_deploy', sa.Text(), nullable=True),
    sa.Column('prev_release', sa.Text(), nullable=True),
    sa.Column('post_release', sa.Text(), nullable=True),
    sa.Column('post_release_delay', sa.Integer(), nullable=True),
    sa.Column('keep_version_num', sa.Integer(), nullable=True),
    sa.Column('repo_url', sa.String(length=200), nullable=True),
    sa.Column('repo_username', sa.String(length=50), nullable=True),
    sa.Column('repo_password', sa.String(length=50), nullable=True),
    sa.Column('repo_mode', sa.String(length=50), nullable=True),
    sa.Column('repo_type', sa.String(length=10), nullable=True),
    sa.PrimaryKeyConstraint('id')
    )
    op.create_table('role',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('name', sa.String(length=30), nullable=True),
    sa.Column('permission_ids', sa.Text(), nullable=True),
    sa.Column('created_at', sa.DateTime(), nullable=True),
    sa.Column('updated_at', sa.DateTime(), nullable=True),
    sa.PrimaryKeyConstraint('id')
    )
    op.create_table('tag',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('name', sa.String(length=30), nullable=True),
    sa.Column('label', sa.String(length=30), nullable=True),
    sa.Column('created_at', sa.DateTime(), nullable=True),
    sa.Column('updated_at', sa.DateTime(), nullable=True),
    sa.PrimaryKeyConstraint('id')
    )
    op.create_table('task',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('user_id', sa.Integer(), nullable=True),
    sa.Column('project_id', sa.Integer(), nullable=True),
    sa.Column('action', sa.Integer(), nullable=True),
    sa.Column('status', sa.Integer(), nullable=True),
    sa.Column('title', sa.String(length=100), nullable=True),
    sa.Column('link_id', sa.String(length=100), nullable=True),
    sa.Column('ex_link_id', sa.String(length=100), nullable=True),
    sa.Column('servers', sa.Text(), nullable=True),
    sa.Column('commit_id', sa.String(length=40), nullable=True),
    sa.Column('branch', sa.String(length=100), nullable=True),
    sa.Column('file_transmission_mode', sa.Integer(), nullable=True),
    sa.Column('file_list', sa.Text(), nullable=True),
    sa.Column('enable_rollback', sa.Integer(), nullable=True),
    sa.Column('created_at', sa.DateTime(), nullable=True),
    sa.Column('updated_at', sa.DateTime(), nullable=True),
    sa.PrimaryKeyConstraint('id')
    )
    op.create_table('task_record',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('stage', sa.String(length=20), nullable=True),
    sa.Column('sequence', sa.Integer(), nullable=True),
    sa.Column('user_id', sa.Integer(), nullable=True),
    sa.Column('task_id', sa.Integer(), nullable=True),
    sa.Column('status', sa.Integer(), nullable=True),
    sa.Column('command', sa.String(length=200), nullable=True),
    sa.Column('success', sa.String(length=2000), nullable=True),
    sa.Column('error', sa.String(length=2000), nullable=True),
    sa.PrimaryKeyConstraint('id')
    )
    op.create_table('user',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('username', sa.String(length=50), nullable=True),
    sa.Column('is_email_verified', sa.Integer(), nullable=True),
    sa.Column('email', sa.String(length=50), nullable=False),
    sa.Column('password', sa.String(length=50), nullable=False),
    sa.Column('avatar', sa.String(length=100), nullable=True),
    sa.Column('role_id', sa.Integer(), nullable=True),
    sa.Column('status', sa.Integer(), nullable=True),
    sa.Column('created_at', sa.DateTime(), nullable=True),
    sa.Column('updated_at', sa.DateTime(), nullable=True),
    sa.PrimaryKeyConstraint('id'),
    sa.UniqueConstraint('email')
    )
    op.create_table('user_group',
    sa.Column('id', sa.Integer(), nullable=False),
    sa.Column('user_id', sa.Integer(), nullable=True),
    sa.Column('group_id', sa.Integer(), nullable=True),
    sa.Column('created_at', sa.DateTime(), nullable=True),
    sa.Column('updated_at', sa.DateTime(), nullable=True),
    sa.ForeignKeyConstraint(['group_id'], ['tag.id'], ),
    sa.ForeignKeyConstraint(['user_id'], ['user.id'], ),
    sa.PrimaryKeyConstraint('id')
    )
    # ### end Alembic commands ###


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.drop_table('user_group')
    op.drop_table('user')
    op.drop_table('task_record')
    op.drop_table('task')
    op.drop_table('tag')
    op.drop_table('role')
    op.drop_table('project')
    op.drop_table('foo')
    op.drop_table('environment')
    # ### end Alembic commands ###
