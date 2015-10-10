<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\models\Group;

class m151010_050344_group_user_admin extends Migration
{
    public function up()
    {
        $this->addColumn(Group::tableName(), 'type', Schema::TYPE_SMALLINT . '(1) DEFAULT 0 COMMENT "用户在项目中的关系类型 0普通用户， 1管理员"');
        $this->alterColumn(Group::tableName(), 'user_id', Schema::TYPE_INTEGER . '(32) NOT NULL COMMENT "用户id"');
    }

    public function down()
    {
        $this->dropColumn(Group::tableName(), 'type');
        echo "m151010_050344_group_user_admin  be reverted.\n";

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
