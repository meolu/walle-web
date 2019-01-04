"""07_project_include

Revision ID: 5ff964e844a7
Revises: 0af33c7b8832
Create Date: 2019-01-04 18:00:58.941866

"""
from alembic import op
import sqlalchemy as sa


revision = '5ff964e844a7'
down_revision = '0af33c7b8832'
branch_labels = None
depends_on = None


def upgrade():
    op.add_column('projects', sa.Column('is_include', sa.Integer(), nullable=True, server_default='0'))


def downgrade():
    op.drop_column('projects', 'is_include')
