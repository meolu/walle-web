"""preject_remove_server

Revision ID: 9532a372b5aa
Revises: 00adfdca30bf
Create Date: 2018-12-24 21:02:57.555145

"""
from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision = '9532a372b5aa'
down_revision = '00adfdca30bf'
branch_labels = None
depends_on = None


def upgrade():
    op.drop_column('projects', 'target_user')
    op.drop_column('projects', 'target_port')


def downgrade():
    op.add_column('projects', sa.Column('target_user', sa.String(100), nullable=False))
    op.add_column('projects', sa.Column('target_port', sa.String(100), nullable=False))
