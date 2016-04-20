<?php

use yii\db\Migration;
use yii\db\Schema;
use app\models\Task;

class m160420_015223_add_file_transmission_mode extends Migration
{
    public function up()
    {

        $this->alterColumn(Task::tableName(), 'created_at', Schema::TYPE_DATETIME . ' COMMENT "创建时间" AFTER enable_rollback');
        $this->alterColumn(Task::tableName(), 'updated_at', Schema::TYPE_DATETIME . ' COMMENT "修改时间" AFTER created_at');

        $this->addColumn(Task::tableName(), 'file_transmission_mode', Schema::TYPE_SMALLINT . '(3) NOT NULL DEFAULT 1 COMMENT "上线文件模式: 1.全量所有文件 2.指定文件列表" AFTER branch');
    }

    public function down()
    {
        $this->dropColumn(Task::tableName(), 'file_transmission_mode');
        echo "m160420_015223_add_file_transmission_mode was reverted.\n";

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
