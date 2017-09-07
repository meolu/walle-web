<?php

use app\models\Task;
use yii\db\Migration;
use yii\db\Schema;

class m170907_102751_add_server_publish_mode extends Migration
{
    public function safeUp()
    {
        $this->addColumn(Task::tableName(), 'server_publish_mode', Schema::TYPE_SMALLINT . '(3) NOT NULL DEFAULT 1 COMMENT "服务器发布模式： 1. 全部服务器 2. 部分服务器" AFTER file_list');
        $this->addColumn(Task::tableName(), 'hosts', Schema::TYPE_TEXT . ' COMMENT "目标机器列表" AFTER server_publish_mode');

        return true;
    }

    public function safeDown()
    {
        $this->dropColumn(Task::tableName(), 'server_publish_mode');
        $this->dropColumn(Task::tableName(), 'hosts');

        echo "m170907_102751_add_server_publish_mode cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170907_102751_add_server_publish_mode cannot be reverted.\n";

        return false;
    }
    */
}
