<?php

use yii\db\Schema;

class m140328_144900_init extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('user', [
            'id' => Schema::TYPE_PK,
            'username' => Schema::TYPE_STRING . ' NOT NULL',
            'is_email_verified' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            'auth_key' => Schema::TYPE_STRING . '(32) NOT NULL',
            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'password_reset_token' => Schema::TYPE_STRING,
            'email_confirmation_token' => Schema::TYPE_STRING,
            'email' => Schema::TYPE_STRING . ' NOT NULL',
            'role' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',

            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
            'created_at' => Schema::TYPE_DATETIME . ' NOT NULL',
            'updated_at' => Schema::TYPE_DATETIME . ' NOT NULL',
            'realname' => Schema::TYPE_STRING . '(32) NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB');

        $this->createTable('conf', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . '(21) unsigned NOT NULL COMMENT "添加项目的用户id"',
            'name' => Schema::TYPE_STRING . '(100) NOT NULL DEFAULT 0 COMMENT "项目名字"',
            'conf' => Schema::TYPE_STRING . '(32) NOT NULL COMMENT "项目配置的名字"',
            'level' => Schema::TYPE_SMALLINT . '(1) NOT NULL COMMENT "项目环境1：测试，2：仿真，3：线上"',
            'status' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 1 COMMENT "状态0：无效，1有效"',
            'version' => Schema::TYPE_STRING . '(32) DEFAULT NULL COMMENT "线上当前版本，用于快速回滚"',
            'created_at' => Schema::TYPE_INTEGER . '(10) DEFAULT 0 COMMENT "创建时间"',
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB');

        $this->createTable('task', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . '(21) unsigned NOT NULL COMMENT "用户id"',
            'project_id' => Schema::TYPE_INTEGER . '(21) NOT NULL DEFAULT 0 COMMENT "项目id"',
            'action' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 0 COMMENT "0全新上线，2回滚"',
            'status' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 0 COMMENT "状态0：新建提交，1审核通过，2审核拒绝，3上线完成，4上线失败"',
            'title' => Schema::TYPE_STRING . '(100) DEFAULT "" COMMENT "上线的软链号"',
            'link_id' => Schema::TYPE_STRING . '(20) DEFAULT "" COMMENT "上线的软链号"',
            'ex_link_id' => Schema::TYPE_STRING . '(20) DEFAULT "" COMMENT "上一次上线的软链号"',
            'commit_id' => Schema::TYPE_STRING . '(32) DEFAULT "" COMMENT "commit id"',
            'created_at' => Schema::TYPE_INTEGER . '(10) DEFAULT 0 COMMENT "创建时间"',
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB');

        $this->createTable('record', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . '(21) unsigned NOT NULL COMMENT "用户id"',
            'task_id' => Schema::TYPE_BIGINT . '(21) NOT NULL COMMENT "任务id"',
            'status' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 1 COMMENT "状态1：成功，0失败"',
            'action' => Schema::TYPE_SMALLINT . '(1) NOT NULL DEFAULT 0 COMMENT "1本地代码更新，2同步代码到服务器，4软链接"',
            'command' => Schema::TYPE_TEXT . ' COMMENT "运行命令"',
            'duration' => Schema::TYPE_INTEGER . '(10) DEFAULT 0 COMMENT "耗时，单位ms"',
            'memo' => Schema::TYPE_TEXT . ' COMMENT "日志/备注"',
            'created_at' => Schema::TYPE_INTEGER . '(10) DEFAULT 0 COMMENT "创建时间"',
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB');

    }

    public function down()
    {
        $this->dropTable('user');
        $this->dropTable('conf');
        $this->dropTable('task');
        $this->dropTable('record');
    }
}
