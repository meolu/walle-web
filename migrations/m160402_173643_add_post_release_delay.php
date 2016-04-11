<?php

use app\models\Project;
use yii\db\Schema;
use yii\db\Migration;

class m160402_173643_add_post_release_delay extends Migration
{
    public function up()
    {
        $this->addColumn(Project::tableName(), 'post_release_delay', Schema::TYPE_INTEGER . '(11) NOT NULL DEFAULT 0 COMMENT "每台目标机执行post_release任务间隔/延迟时间 单位:秒" AFTER post_release');

    }

    public function down()
    {
        $this->dropColumn(Project::tableName(), 'post_release_delay');
        echo "m160402_173643_add_post_release_delay was reverted.\n";

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
