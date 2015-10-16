<?php

use yii\db\Migration;

class m151012_135612_task_add_branch extends Migration
{
    public function up()
    {
        $this->addColumn(\app\models\Task::tableName(), 'branch', \yii\db\Schema::TYPE_STRING . '(100) DEFAULT "master" comment "选择上线的分支"');
        $this->alterColumn(\app\models\Project::tableName(), 'name', \yii\db\Schema::TYPE_STRING . '(100) DEFAULT "master" comment "项目名字"');
    }

    public function down()
    {
        $this->dropColumn(\app\models\Task::tableName(), 'branch');
        echo "m151012_135612_task_add_branch  be reverted.\n";

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
