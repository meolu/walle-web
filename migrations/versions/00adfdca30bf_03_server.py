"""server

Revision ID: 00adfdca30bf
Revises: 52a2df18b1d4
Create Date: 2018-12-23 20:39:35.200263

"""
import sqlalchemy as sa
from alembic import op

# revision identifiers, used by Alembic.
revision = '00adfdca30bf'
down_revision = '52a2df18b1d4'
branch_labels = None
depends_on = None


def upgrade():
    op.add_column('servers', sa.Column('user', sa.String(100), nullable=False))


def downgrade():
    op.drop_column('servers', 'user')
    pass
