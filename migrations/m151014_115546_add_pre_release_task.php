<?php

use yii\db\Migration;
use app\models\Project;
use \yii\db\mysql\Schema;

class m151014_115546_add_pre_release_task extends Migration
{
    public function up()
    {
        $this->alterColumn(Project::tableName(), 'post_release', Schema::TYPE_TEXT . ' COMMENT "同步之前目标机器执行的任务"');
        $this->renameColumn(Project::tableName(), 'post_release', 'pre_release');
        $this->addColumn(Project::tableName(), 'post_release', Schema::TYPE_TEXT . ' COMMENT "同步之后目标机器执行的任务" AFTER pre_release');

    }

    public function down()
    {
        $this->dropColumn(Project::tableName(), 'post_release');
        $this->renameColumn(Project::tableName(), 'pre_release', 'post_release');
        echo "m151014_115546_add_pre_release_task be reverted.\n";

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
