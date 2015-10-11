<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\models\Project;

class m151011_054352_task_need_more_long extends Migration
{
    public function up()
    {
        $this->alterColumn(Project::tableName(), 'excludes', Schema::TYPE_TEXT . ' COMMENT "要排除的文件"');
        $this->alterColumn(Project::tableName(), 'hosts', Schema::TYPE_TEXT . ' COMMENT "目标机器列表"');
        $this->alterColumn(Project::tableName(), 'pre_deploy', Schema::TYPE_TEXT . ' COMMENT "部署前置任务"');
        $this->alterColumn(Project::tableName(), 'post_deploy', Schema::TYPE_TEXT . ' COMMENT "同步之前任务"');
        $this->alterColumn(Project::tableName(), 'post_release', Schema::TYPE_TEXT . ' COMMENT "同步之后任务"');
    }

    public function down()
    {
        echo "m151011_054352_task_need_more_long cannot be reverted.\n";

        return false;
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
