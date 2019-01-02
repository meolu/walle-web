"""06_task_rollback

Revision ID: 0af33c7b8832
Revises: 91c4d13540c3
Create Date: 2018-12-31 17:04:39.514132

"""
from alembic import op
import sqlalchemy as sa


revision = '0af33c7b8832'
down_revision = '91c4d13540c3'
branch_labels = None
depends_on = None


def upgrade():
    op.alter_column('tasks', 'enable_rollback', new_column_name='is_rollback', existing_type=sa.Integer())


def downgrade():
    op.alter_column('tasks', 'is_rollback', new_column_name='enable_rollback', existing_type=sa.Integer())
