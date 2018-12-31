#!/usr/bin/env python
#  -*- coding: utf-8 -*-
"""init walle database

此刻是walle 2.0 alpha准备工作收尾阶段中, 但内心非常孤独, 大多用户让人心寒, 缺乏基本的感恩之心

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
    init_environments()
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

    # commit
    db.session.commit()


def create_environments():
    sql = u"""CREATE TABLE `environments` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '',
              `name` varchar(100) DEFAULT 'master' COMMENT '',
              `space_id` int(10) NOT NULL DEFAULT '0' COMMENT '',
              `status` tinyint(1) DEFAULT '1' COMMENT '',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
    db.session.execute(sql)


def init_environments():
    sql = u"""INSERT INTO `environments` VALUES
            (1,'开发环境', 1, 1, '2017-03-08 17:26:07', '2018-11-26 15:38:14'),
            (2,'测试环境', 1, 1, '2017-05-13 11:26:42', '2018-11-26 15:38:14'),
            (3,'生产环境', 1, 1, '2017-05-14 10:46:31', '2018-11-26 17:10:02');"""
    db.session.execute(sql)


def create_menus():
    sql = u"""CREATE TABLE `menus` (
              `id` int(15) NOT NULL AUTO_INCREMENT,
              `name_cn` varchar(30) NOT NULL COMMENT '',
              `name_en` varchar(30) NOT NULL COMMENT '',
              `pid` int(6) NOT NULL COMMENT '',
              `type` enum('action','controller','module') DEFAULT 'action' COMMENT '',
              `sequence` int(11) DEFAULT '0' COMMENT '',
              `role` varchar(10) NOT NULL DEFAULT '' COMMENT '',
              `archive` tinyint(1) DEFAULT '0' COMMENT '',
              `icon` varchar(30) DEFAULT '' COMMENT '',
              `url` varchar(100) DEFAULT '' COMMENT '',
              `visible` tinyint(1) DEFAULT '1' COMMENT '',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
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
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '',
              `user_id` int(10) NOT NULL COMMENT '',
              `name` varchar(100) DEFAULT 'master' COMMENT '',
              `environment_id` int(1) NOT NULL COMMENT '',
              `space_id` int(10) NOT NULL DEFAULT '0' COMMENT '',
              `status` tinyint(1) DEFAULT '1' COMMENT '',
              `master` varchar(100) NOT NULL DEFAULT '' COMMENT '',
              `version` varchar(40) DEFAULT '' COMMENT '',
              `excludes` text COMMENT '',
              `target_user` varchar(50) NOT NULL COMMENT '',
              `target_port` int(3) NOT NULL DEFAULT '22' COMMENT '',
              `target_root` varchar(200) NOT NULL COMMENT '',
              `target_releases` varchar(200) NOT NULL COMMENT '',
              `server_ids` text COMMENT '',
              `task_vars` text COMMENT '',
              `prev_deploy` text COMMENT '',
              `post_deploy` text COMMENT '',
              `prev_release` text COMMENT '',
              `post_release` text COMMENT '',
              `keep_version_num` int(3) NOT NULL DEFAULT '20' COMMENT '',
              `repo_url` varchar(200) DEFAULT '' COMMENT '',
              `repo_username` varchar(50) DEFAULT '' COMMENT '',
              `repo_password` varchar(50) DEFAULT '' COMMENT '',
              `repo_mode` varchar(50) DEFAULT 'branch' COMMENT '',
              `repo_type` varchar(10) DEFAULT 'git' COMMENT '',
              `notice_type` varchar(10) NOT NULL DEFAULT '' COMMENT '',
              `notice_hook` text NOT NULL COMMENT '',
              `task_audit` tinyint(1) DEFAULT '0' COMMENT '',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
    db.session.execute(sql)


def create_records():
    sql = u"""CREATE TABLE `records` (
              `id` bigint(10) NOT NULL AUTO_INCREMENT COMMENT '',
              `stage` varchar(20) DEFAULT NULL COMMENT '',
              `sequence` int(10) DEFAULT NULL COMMENT '',
              `user_id` int(21) unsigned NOT NULL COMMENT '',
              `task_id` bigint(11) NOT NULL COMMENT '',
              `status` int(3) NOT NULL COMMENT '',
              `host` varchar(200) DEFAULT '' COMMENT '',
              `user` varchar(200) DEFAULT '' COMMENT '',
              `command` text COMMENT '',
              `success` LONGTEXT COMMENT '',
              `error` LONGTEXT COMMENT '',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
    db.session.execute(sql)


def create_servers():
    sql = u"""CREATE TABLE `servers` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '',
              `name` varchar(100) DEFAULT '' COMMENT '',
              `host` varchar(100) NOT NULL COMMENT '',
              `port` int(1) DEFAULT '22' COMMENT '',
              `status` tinyint(1) DEFAULT '1' COMMENT '',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
    db.session.execute(sql)


def create_spaces():
    sql = u"""CREATE TABLE `spaces` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '',
              `user_id` int(10) NOT NULL COMMENT '',
              `name` varchar(100) NOT NULL COMMENT '',
              `status` tinyint(1) DEFAULT '1' COMMENT '',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
    db.session.execute(sql)


def init_spaces():
    sql = u"""INSERT INTO `spaces` VALUES
            (1,2,'Demo空间',1,'2018-09-17 22:09:37','2018-11-18 00:09:58');"""
    db.session.execute(sql)


def create_tasks():
    sql = u"""CREATE TABLE `tasks` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '',
              `name` varchar(100) NOT NULL COMMENT '',
              `user_id` bigint(21) unsigned NOT NULL COMMENT '',
              `project_id` int(11) NOT NULL COMMENT '',
              `action` int(1) DEFAULT '0' COMMENT '',
              `status` tinyint(1) NOT NULL COMMENT '',
              `link_id` varchar(100) DEFAULT '' COMMENT '',
              `ex_link_id` varchar(100) DEFAULT '' COMMENT '',
              `servers` text COMMENT '',
              `commit_id` varchar(40) DEFAULT '' COMMENT '',
              `branch` varchar(100) DEFAULT 'master' COMMENT '',
              `tag` varchar(100) DEFAULT '' COMMENT '',
              `file_transmission_mode` smallint(3) NOT NULL DEFAULT '1' COMMENT '',
              `file_list` LONGTEXT COMMENT '',
              `enable_rollback` int(1) NOT NULL DEFAULT '1' COMMENT '',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
    db.session.execute(sql)


def create_users():
    sql = u"""CREATE TABLE `users` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `username` varchar(50) NOT NULL COMMENT '',
              `is_email_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
              `email` varchar(50) NOT NULL COMMENT '',
              `password` varchar(100) NOT NULL COMMENT '',
              `password_hash` varchar(50) DEFAULT NULL COMMENT '',
              `avatar` varchar(100) DEFAULT 'default.jpg' COMMENT '',
              `role` varchar(10) NOT NULL DEFAULT '' COMMENT '',
              `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '',
              `last_space` int(11) NOT NULL DEFAULT '0',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
    db.session.execute(sql)


def init_users():
    sql = u"""INSERT INTO `users` VALUES
            (1,'Super',1,'super@walle-web.io','pbkdf2:sha256:50000$AyRSJVSn$448c69b93158b30b9e3625d340b48dbdbce1186fcf30fc72663a9361ffec339b','','','SUPER',1,0,'2017-03-17 09:03:09','2018-11-24 17:01:23'),
            (2,'Owner',1,'owner@walle-web.io','pbkdf2:sha256:50000$AyRSJVSn$448c69b93158b30b9e3625d340b48dbdbce1186fcf30fc72663a9361ffec339b','','','',1,1,'2017-03-20 19:05:44','2018-11-24 17:01:23'),
            (3,'Master',1,'master@walle-web.io','pbkdf2:sha256:50000$AyRSJVSn$448c69b93158b30b9e3625d340b48dbdbce1186fcf30fc72663a9361ffec339b','','','',1,1,'2017-04-13 15:03:57','2018-11-24 10:22:37'),
            (4,'Developer',1,'developer@walle-web.io','pbkdf2:sha256:50000$AyRSJVSn$448c69b93158b30b9e3625d340b48dbdbce1186fcf30fc72663a9361ffec339b','','','',1,1,'2017-05-11 22:33:35','2018-12-05 19:37:47'),
            (5,'Reporter',1,'reporter@walle-web.io','pbkdf2:sha256:50000$AyRSJVSn$448c69b93158b30b9e3625d340b48dbdbce1186fcf30fc72663a9361ffec339b','','','',1,1,'2017-05-11 23:39:11','2018-11-23 07:40:55')"""
    db.session.execute(sql)


def create_members():
    sql = u"""CREATE TABLE `members` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '',
              `user_id` int(10) DEFAULT '0' COMMENT '',
              `source_id` int(10) DEFAULT '0' COMMENT '',
              `source_type` varchar(10) DEFAULT '' COMMENT '',
              `access_level` varchar(10) DEFAULT '10' COMMENT '',
              `status` tinyint(1) DEFAULT '1' COMMENT '',
              `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '',
              `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';"""
    db.session.execute(sql)


def init_members():
    sql = u"""INSERT INTO `members` VALUES
            (null,2,1,'group','OWNER',1,'2018-12-09 00:35:59','2018-12-09 00:35:59'),
            (null,3,1,'group','MASTER',1,'2018-12-09 00:35:59','2018-12-09 00:35:59'),
            (null,4,1,'group','DEVELOPER',1,'2018-12-09 00:35:59','2018-12-09 00:35:59'),
            (null,5,1,'group','REPORTER',1,'2018-12-09 00:35:59','2018-12-09 00:35:59');"""
    db.session.execute(sql)


def downgrade():
    op.drop_table('members')
    op.drop_table('users')
    op.drop_table('tasks')
    op.drop_table('spaces')
    op.drop_table('servers')
    op.drop_table('records')
    op.drop_table('projects')
    op.drop_table('menus')
    op.drop_table('environments')
