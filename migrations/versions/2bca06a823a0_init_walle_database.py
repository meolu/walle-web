#!/usr/bin/env python
#  -*- coding: utf-8 -*-
"""init walle database

Revision ID: 2bca06a823a0
Revises: 
Create Date: 2018-12-08 21:01:19.273412

"""
from alembic import op
from walle.service.extensions import db

revision = '2bca06a823a0'
down_revision = None
branch_labels = None
depends_on = None


def upgrade():
    # Don't ask why, you are not me, and you will never understand
    # who care about you ?
    create_environments()
    create_menus()
    init_menus()
    create_projects()
    create_records()
    create_servers()
    create_spaces()
    init_spaces()
    create_tasks()
    create_users()
    init_users()
    create_members()
    init_members()


def create_environments():
    sql = u"""CREATE TABLE `environments` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
              `name` varchar(100) DEFAULT 'master' COMMENT '环境名称',
              `space_id` int(10) NOT NULL DEFAULT '0' COMMENT '空间id',
              `status` tinyint(1) DEFAULT '1' COMMENT '状态：0无效，1有效',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='项目环境配置表';"""
    db.session.execute(sql)


def create_menus():
    sql = u"""CREATE TABLE `members` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '记录id',
              `user_id` int(10) DEFAULT '0' COMMENT '用户id',
              `source_id` int(10) DEFAULT '0' COMMENT '归属资源id：space_id | project_id',
              `source_type` varchar(10) DEFAULT '' COMMENT '归属资源类型：space | project',
              `access_level` varchar(10) DEFAULT '10' COMMENT '权限值。10 => Guest access | 20 => Reporter access | 30 => Developer access | 40 => Master access | 50 => Owner access',
              `status` tinyint(1) DEFAULT '1' COMMENT '状态：0无效，1有效',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COMMENT='用户组关联表';"""
    db.session.execute(sql)


def init_menus():
    sql = u"""INSERT INTO `menus` VALUES
            (1,'首页','index',0,'module',10000,'10',0,'wl-icon-main','/',1,'2017-06-11 23:11:38','2018-11-03 09:31:51'),
            (2,'空间管理','space',0,'module',10001,'50',0,'wl-icon-space-set','/space/index',1,'2017-06-11 23:11:38','2018-11-01 07:37:23'),
            (3,'用户管理','user',0,'module',10002,'40',0,'wl-icon-user-set','/user/index',1,'2017-06-11 23:11:52','2018-12-05 19:50:43'),
            (4,'项目中心','project',0,'module',10003,'30',0,'wl-icon-project-set','',1,'2017-06-11 23:12:45','2018-12-05 19:45:43'),
            (5,'部署管理','deploy',0,'module',10101,'10',0,'wl-icon-deploy-set','/deploy/index',1,'2017-06-11 23:13:51','2018-11-04 23:57:19'),
            (6,'环境管理','group',4,'controller',10102,'50',0,'leaf','/environment/index',1,'2017-06-11 23:14:11','2018-11-03 09:31:41'),
            (7,'服务器管理','role',4,'controller',10103,'40',0,'leaf','/server/index',1,'2017-06-11 23:14:44','2018-11-03 09:31:41'),
            (8,'项目管理','environment',4,'controller',10201,'30',0,'leaf','/project/index',1,'2017-06-11 23:15:30','2018-12-05 19:45:12');"""
    db.session.execute(sql)


def create_projects():
    sql = u"""CREATE TABLE `projects` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '记录id',
              `user_id` int(10) NOT NULL COMMENT '添加项目的用户id',
              `name` varchar(100) DEFAULT 'master' COMMENT '项目名字',
              `environment_id` int(1) NOT NULL COMMENT 'environment的id',
              `space_id` int(10) NOT NULL DEFAULT '0' COMMENT '空间id',
              `status` tinyint(1) DEFAULT '1' COMMENT '状态：0无效，1有效',
              `master` varchar(100) NOT NULL DEFAULT '' COMMENT '项目管理员：用户id,用户id',
              `version` varchar(40) DEFAULT '' COMMENT '线上当前版本，用于快速回滚',
              `excludes` text COMMENT '要排除的文件',
              `target_user` varchar(50) NOT NULL COMMENT '目标机器的登录用户',
              `target_port` int(3) NOT NULL DEFAULT '22' COMMENT '目标机器的登录端口',
              `target_root` varchar(200) NOT NULL COMMENT '目标机器的 server 目录',
              `target_releases` varchar(200) NOT NULL COMMENT '目标机器的版本库',
              `server_ids` text COMMENT '目标机器列表',
              `task_vars` text COMMENT '高级环境变量',
              `prev_deploy` text COMMENT '部署前置任务',
              `post_deploy` text COMMENT '同步之前任务',
              `prev_release` text COMMENT '同步之前目标机器执行的任务',
              `post_release` text COMMENT '同步之后目标机器执行的任务',
              `keep_version_num` int(3) NOT NULL DEFAULT '20' COMMENT '线上版本保留数',
              `repo_url` varchar(200) DEFAULT '' COMMENT 'git地址',
              `repo_username` varchar(50) DEFAULT '' COMMENT '版本管理系统的用户名，一般为svn的用户名',
              `repo_password` varchar(50) DEFAULT '' COMMENT '版本管理系统的密码，一般为svn的密码',
              `repo_mode` varchar(50) DEFAULT 'branch' COMMENT '上线方式：branch/tag',
              `repo_type` varchar(10) DEFAULT 'git' COMMENT '上线方式：git/svn',
              `notice_type` varchar(10) NOT NULL DEFAULT '' COMMENT '通知方式：sms 短信、dingding 钉钉、email 邮件',
              `notice_hook` text NOT NULL COMMENT '通知地址：sms 手机号(英文分号分割)、dingding webhook、email 邮箱 地址(英文分号分割)',
              `task_audit` tinyint(1) DEFAULT '0' COMMENT '是否开启审核：0 不开启，1 开启',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='项目配置表';"""
    db.session.execute(sql)


def create_records():
    sql = u"""CREATE TABLE `records` (
              `id` bigint(10) NOT NULL AUTO_INCREMENT COMMENT '记录id',
              `stage` varchar(20) DEFAULT NULL COMMENT '阶段',
              `sequence` int(10) DEFAULT NULL COMMENT '序列号',
              `user_id` int(21) unsigned NOT NULL COMMENT '用户id',
              `task_id` bigint(11) NOT NULL COMMENT 'Task id',
              `status` int(3) NOT NULL COMMENT '状态0：新建提交，1审核通过，2审核拒绝，3上线完成，4上线失败',
              `host` varchar(200) DEFAULT '' COMMENT '命令执行所在机器',
              `user` varchar(200) DEFAULT '' COMMENT '命令执行所在机器的登录用户',
              `command` text COMMENT '命令与参数',
              `success` text COMMENT '成功返回信息',
              `error` text COMMENT '错误信息',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2714 DEFAULT CHARSET=utf8 COMMENT='任务执行记录表';"""
    db.session.execute(sql)


def create_servers():
    sql = u"""CREATE TABLE `servers` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '记录id',
              `name` varchar(100) DEFAULT '' COMMENT 'server name',
              `host` varchar(100) NOT NULL COMMENT 'ip/host',
              `port` int(1) DEFAULT '22' COMMENT 'ssh port',
              `status` tinyint(1) DEFAULT '1' COMMENT '状态：0无效，1有效',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='服务器记录表';"""
    db.session.execute(sql)


def create_spaces():
    sql = u"""CREATE TABLE `spaces` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '记录id',
              `user_id` int(10) NOT NULL COMMENT '空间所有者uid',
              `name` varchar(100) NOT NULL COMMENT '空间名字',
              `status` tinyint(1) DEFAULT '1' COMMENT '状态：0无效，1有效',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='空间配置表';"""
    db.session.execute(sql)


def init_spaces():
    pass


def create_tasks():
    sql = u"""CREATE TABLE `tasks` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '记录id',
              `name` varchar(100) NOT NULL COMMENT '上线单标题',
              `user_id` bigint(21) unsigned NOT NULL COMMENT '用户id',
              `project_id` int(11) NOT NULL COMMENT '项目id',
              `action` int(1) DEFAULT '0' COMMENT '0全新上线，2回滚',
              `status` tinyint(1) NOT NULL COMMENT '# 状态：0新建提交，1审核通过，2审核拒绝，3上线中，4上线完成，5上线失败',
              `link_id` varchar(100) DEFAULT '' COMMENT '上线的软链号',
              `ex_link_id` varchar(100) DEFAULT '' COMMENT '被替换的上次上线的软链号',
              `servers` text COMMENT '上线的机器',
              `commit_id` varchar(40) DEFAULT '' COMMENT 'git commit id',
              `branch` varchar(100) DEFAULT 'master' COMMENT '选择上线的分支',
              `tag` varchar(100) DEFAULT '' COMMENT '选择上线的tag',
              `file_transmission_mode` smallint(3) NOT NULL DEFAULT '1' COMMENT '上线文件模式: 1.全量所有文件 2.指定文件列表',
              `file_list` text COMMENT '文件列表，svn上线方式可能会产生',
              `enable_rollback` int(1) NOT NULL DEFAULT '1' COMMENT '能否回滚此版本0：no 1：yes',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='上线单记录表';"""
    db.session.execute(sql)


def create_users():
    sql = u"""CREATE TABLE `users` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `username` varchar(50) NOT NULL COMMENT '用户昵称',
              `is_email_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否邮箱认证',
              `email` varchar(50) NOT NULL COMMENT '邮箱',
              `password` varchar(100) NOT NULL COMMENT '密码',
              `password_hash` varchar(50) DEFAULT NULL COMMENT 'hash',
              `avatar` varchar(100) DEFAULT 'default.jpg' COMMENT '头像图片地址',
              `role` varchar(10) NOT NULL DEFAULT '' COMMENT '角色',
              `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态: 0新建，1正常，2冻结',
              `last_space` int(11) NOT NULL DEFAULT '0',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='用户表';"""
    db.session.execute(sql)


def init_users():
    pass


def create_members():
    sql = u"""CREATE TABLE `members` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '记录id',
              `user_id` int(10) DEFAULT '0' COMMENT '用户id',
              `source_id` int(10) DEFAULT '0' COMMENT '归属资源id：space_id | project_id',
              `source_type` varchar(10) DEFAULT '' COMMENT '归属资源类型：space | project',
              `access_level` varchar(10) DEFAULT '10' COMMENT '权限值。10 => Guest access | 20 => Reporter access | 30 => Developer access | 40 => Master access | 50 => Owner access',
              `status` tinyint(1) DEFAULT '1' COMMENT '状态：0无效，1有效',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COMMENT='用户组关联表';"""
    db.session.execute(sql)


def init_members():
    pass


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.drop_table('members')
    op.drop_table('users')
    op.drop_table('tasks')
    op.drop_table('spaces')
    op.drop_table('servers')
    op.drop_table('records')
    op.drop_table('projects')
    op.drop_table('menus')
    op.drop_table('environments')
    # ### end Alembic commands ###
