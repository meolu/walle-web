# -*- coding: utf-8 -*-
"""Database module, including the SQLAlchemy database object and DB-related utilities."""

# flake8: noqa  # flake8 has real problems linting this file on Python 2

from pprint import pformat

from sqlalchemy import desc, or_
from sqlalchemy.sql.sqltypes import Date, DateTime
from werkzeug import cached_property
from flask import current_app
from walle.service.extensions import db
from walle.service.utils import basestring
from walle.service.utils import datetime_str_to_obj, date_str_to_obj

# Alias common SQLAlchemy names
Column = db.Column
relationship = db.relationship

OPERATOR_FUNC_DICT = {
    '=': (lambda cls, k, v: getattr(cls, k) == v),
    '==': (lambda cls, k, v: getattr(cls, k) == v),
    'eq': (lambda cls, k, v: getattr(cls, k) == v),
    '!=': (lambda cls, k, v: getattr(cls, k) != v),
    'ne': (lambda cls, k, v: getattr(cls, k) != v),
    'neq': (lambda cls, k, v: getattr(cls, k) != v),
    '>': (lambda cls, k, v: getattr(cls, k) > v),
    'gt': (lambda cls, k, v: getattr(cls, k) > v),
    '>=': (lambda cls, k, v: getattr(cls, k) >= v),
    'gte': (lambda cls, k, v: getattr(cls, k) >= v),
    '<': (lambda cls, k, v: getattr(cls, k) < v),
    'lt': (lambda cls, k, v: getattr(cls, k) < v),
    '<=': (lambda cls, k, v: getattr(cls, k) <= v),
    'lte': (lambda cls, k, v: getattr(cls, k) <= v),
    'or': (lambda cls, k, v: or_(getattr(cls, k) == value for value in v)),
    'in': (lambda cls, k, v: getattr(cls, k).in_(v)),
    'nin': (lambda cls, k, v: ~getattr(cls, k).in_(v)),
    'like': (lambda cls, k, v: getattr(cls, k).like('%%%s%%' % (v))),
    'nlike': (lambda cls, k, v: ~getattr(cls, k).like(v)),
    '+': (lambda cls, k, v: getattr(cls, k) + v),
    'incr': (lambda cls, k, v: getattr(cls, k) + v),
    '-': (lambda cls, k, v: getattr(cls, k) - v),
    'decr': (lambda cls, k, v: getattr(cls, k) - v),
}


def parse_operator(cls, filter_name_dict):
    """ 用来返回sqlalchemy query对象filter使用的表达式
    Args:
        filter_name_dict (dict): 过滤条件dict
        {
            'last_name': {'eq': 'wang'},    # 如果是dic使用key作为操作符
            'age': {'>': 12}
        }
    Returns:
        binary_expression_list (lambda list)
    """

    def _change_type(cls, field, value):
        """ 有些表字段比如DateTime类型比较的时候需要转换类型，
        前端传过来的都是字符串，Date等类型没法直接相比较，需要转成Date类型
        Args:
            cls (class): Model class
            field (str): Model class field
            value (str): value need to compare
        """
        field_type = getattr(cls, field).type
        if isinstance(field_type, Date):
            return date_str_to_obj(value)
        elif isinstance(field_type, DateTime):
            return datetime_str_to_obj(value)
        else:
            return value

    binary_expression_list = []
    for field, op_dict in list(filter_name_dict.items()):
        for op, op_val in list(op_dict.items()):
            op_val = _change_type(cls, field, op_val)
            if op in OPERATOR_FUNC_DICT:
                binary_expression_list.append(
                        OPERATOR_FUNC_DICT[op](cls, field, op_val)
                )
    return binary_expression_list


class CRUDMixin(object):
    """Mixin that adds convenience methods for
    CRUD (create, read, update, delete) operations."""

    @classmethod
    def create(cls, **kwargs):
        """Create a new record and save it the database."""
        instance = cls(**kwargs)
        return instance.save()

    @classmethod
    def create_from_dict(cls, d):
        """Create a new record and save it the database."""
        assert isinstance(d, dict)
        instance = cls(**d)
        return instance.save()

    def update(self, commit=True, **kwargs):
        """Update specific fields of a record."""
        for attr, value in list(kwargs.items()):
            setattr(self, attr, value)
        return commit and self.save() or self

    def save(self, commit=True):
        """Save the record."""
        db.session.add(self)
        if commit:
            try:
                db.session.commit()
            except Exception as e:
                current_app.logger.info(e)
                db.session.rollback()
        return self

    def delete(self, commit=True):
        """Remove the record from the database."""
        db.session.delete(self)
        if commit:
            try:
                db.session.commit()
            except:
                db.session.rollback()
        return self

    def to_dict(self, fields_list=None):
        """
        Args:
            fields (str list): 指定返回的字段
        """
        column_list = fields_list or [
            column.name for column in self.__table__.columns
            ]
        return {
            column_name: getattr(self, column_name)
            for column_name in column_list
            }

    @classmethod
    def create_or_update(cls, query_dict, update_dict=None):
        instance = db.session.query(cls).filter_by(**query_dict).first()
        if instance:  # update
            if update_dict is not None:
                return instance.update(**update_dict)
            else:
                return instance
        else:  # create new instance
            query_dict.update(update_dict or {})
            return cls.create(**query_dict)

    @classmethod
    def query_paginate(cls, page=1, limit=20, fields=None, order_by_list=[('id', 'desc')],
                       filter_name_dict=None):
        """ 通用的分页查询函数
        Args:
            page (int): 页数
            limit (int): 每页个数
            order_by_list (tuple list): 用来指定排序的字段，可以传多个
                [ ('id', 1), ('name', -1) ]，1表示正序，-1表示逆序
                or
                [ ('id', 'asc'), ('name', 'desc') ]，1表示正序，-1表示逆序

            filter_name_dict (dict): 过滤条件，使用字典表示，使用字段名作为key，value
                是{'operator': to_compare_value}, e.g.:
                {
                    'last_name': {'eq': 'wang'},  # 如果是dic使用key作为操作符
                    'age': {'>': 12}
                }

        Returns:
            if fields is not None:
                (keytuple_list, total_cnt) (tuple)
            else:
                (instance_list, total_cnt) (tuple)

        前段查询参数规范：
        request.args 示例：
        ImmutableMultiDict([('limit', '10'), ('page', '1'), ('filter', '[{"field":"name","op":"eq","q":"g"},{
        "field":"id","op":"gt","q":"5"
        }]')])

        page: 页码
        limit: 每页限制
        order: 顺序，取值"asc" "desc". """'name', 'asc', 'model', 'desc'"""
        fields: 需要返回的字段
        filter: 过滤条件：
        {
            field: 需要过滤的字段
            op: 过滤操作符,支持"eq","neq","gt","gte","lt","lte","in","nin","like"
            q: 过滤值
        }
        """
        fields = (
            [getattr(cls, column) for column in fields] if fields is not None
            else None
        )
        if fields:
            query = db.session.query(*fields)
        else:
            query = db.session.query(cls)
        if order_by_list:
            for (field, order) in order_by_list:
                query = (
                    query.order_by(getattr(cls, field)) if order == 1 else
                    query.order_by(desc(getattr(cls, field)))
                )

        if filter_name_dict:
            p = parse_operator(cls, filter_name_dict)
            query = query.filter(*p)

        total_cnt = query.count()
        start = (page - 1) * limit
        query = query.offset(start).limit(limit)
        instance_or_keytuple_list = query.all()
        return instance_or_keytuple_list, total_cnt

    @classmethod
    def dump_schema(cls, items, fields, schema_class):
        """ 用来序列化从数据库查询出来的对象
        Args:
            items (instance list): obj list query from mysql
            fields (str list): fields need to dump
            schema_class (marshmallow.Schema): marshmallow.Schema class
        Returns:
            items, err
        """
        fields = (
            fields if fields else list(schema_class._declared_fields.keys())
        )
        schema = schema_class(many=True, only=fields)
        items, err = schema.dump(items)
        return items, err

    @classmethod
    def query_paginate_and_dump_schema(cls, page=1, limit=20, fields=None,
                                       order_by_list=None,
                                       filter_name_dict=None,
                                       schema_class=None):
        """ 分页查询并且返回dump后的对象，可以解决大部分查询问题 """
        assert schema_class
        items, total_cnt = cls.query_paginate(
                page, limit, fields, order_by_list, filter_name_dict
        )
        items, err = cls.dump_schema(items, fields, schema_class)
        return items, total_cnt

    def __repr__(self):
        return pformat(self.to_dict())

    @cached_property
    def column_name_set(self):
        return set([column.name for column in self.__table__.columns])

    @classmethod
    def get_common_fields(cls, fields=None):
        """ 防止传过来的fields含有该Model没有的字段 """
        if not fields:
            return []
        table_fields_set = set(
                [column.name for column in cls.__table__.columns]
        )
        return list(table_fields_set & set(fields))


class Model(CRUDMixin, db.Model):
    """Base model class that includes CRUD convenience methods."""

    __abstract__ = True

    status_remove = -1
    status_default = 0
    status_available = 1


# From Mike Bayer's "Building the app" talk
# https://speakerdeck.com/zzzeek/building-the-app
class SurrogatePK(object):
    """A mixin that adds a surrogate integer 'primary key' column named ``id`` to any declarative-mapped class."""

    __table_args__ = {'extend_existing': True}

    id = db.Column(db.Integer, primary_key=True)

    @classmethod
    def get_by_id(cls, record_id):
        """Get record by ID."""
        if any(
                (isinstance(record_id, basestring) and record_id.isdigit(),
                 isinstance(record_id, (int))),
        ):
            return cls.query.get(int(record_id))
        return None


def reference_col(tablename, nullable=False, pk_name='id', **kwargs):
    """Column that adds primary key foreign key reference.

    Usage: ::

        category_id = reference_col('category')
        category = relationship('Category', backref='categories')
    """
    return db.Column(
            db.ForeignKey('{0}.{1}'.format(tablename, pk_name)),
            nullable=nullable, **kwargs)
