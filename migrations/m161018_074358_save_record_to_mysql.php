<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Record;

class m161018_074358_save_record_to_mysql extends Migration
{
    public function up()
    {

        $this->addColumn(Record::tableName(), 'execute_at', Schema::TYPE_DATETIME . ' COMMENT "命令执行的开始时间" AFTER  action');
        $this->addColumn(Record::tableName(), 'server_user', Schema::TYPE_STRING . '(50) COMMENT "server user" AFTER execute_at');
        $this->addColumn(Record::tableName(), 'server_name', Schema::TYPE_STRING . '(64) COMMENT "server name" AFTER server_user');
        $this->addColumn(Record::tableName(), 'server_directory', Schema::TYPE_STRING . '(1024) COMMENT "执行命令的server目录" AFTER server_name');
        $this->alterColumn(Record::tableName(), 'action', Schema::TYPE_STRING . '(32) COMMENT "部署到了哪个阶段"');
        $this->createIndex('idx_show_console', Record::tableName(), ['task_id']);
        return true;
    }

    public function down()
    {

        $this->dropColumn(Record::tableName(), 'execute_at');
        $this->dropColumn(Record::tableName(), 'server_user');
        $this->dropColumn(Record::tableName(), 'server_name');
        $this->dropColumn(Record::tableName(), 'server_directory');
        $this->dropIndex('idx_show_console', Record::tableName());

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
