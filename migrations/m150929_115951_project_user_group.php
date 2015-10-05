<?php

use yii\db\Schema;
use yii\db\Migration;

class m150929_115951_project_user_group extends Migration
{
    public function up() {
        // 项目中用户关系
        $this->createTable('group', [
            'id' => Schema::TYPE_PK,
            'project_id' => Schema::TYPE_INTEGER . '(2) unsigned NOT NULL COMMENT "项目id"',
            'user_id' => Schema::TYPE_STRING . '(32) NOT NULL COMMENT "用户id"',
        ]);
        // 添加头像
        $this->addColumn(\app\models\User::tableName(), 'avatar', Schema::TYPE_STRING . '(100) DEFAULT "default.jpg" COMMENT "头像图片地址" AFTER email');
    }

    public function down()
    {
        $this->dropTable('group');
        $this->dropColumn(\app\models\User::tableName(), 'avatar');

        return true;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
