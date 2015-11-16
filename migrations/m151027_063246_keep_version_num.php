<?php

use yii\db\Migration;
use \app\models\Project;
use \yii\db\mssql\Schema;
use app\models\Task;

class m151027_063246_keep_version_num extends Migration
{
    public function up()
    {
        $this->addColumn(Project::tableName(), 'keep_version_num', Schema::TYPE_INTEGER . '(3) NOT NULL DEFAULT 20 COMMENT "线上版本保留数" AFTER audit');
        $this->addColumn(Task::tableName(), 'enable_rollback', Schema::TYPE_INTEGER . '(1) NOT NULL DEFAULT 1 COMMENT "能否回滚此版本:0no 1yes"');

    }

    public function down()
    {
        $this->dropColumn(Project::tableName(), 'keep_version_num');
        $this->dropColumn(Task::tableName(), 'enable_rollback');
        echo "m151027_063246_keep_version_num was reverted.\n";

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
