<?php

use yii\db\Schema;
use yii\db\Migration;

class m151005_001053_alter_conf_2_project extends Migration
{
    public function up()
    {
        $this->renameTable('conf', 'project');

    }

    public function down()
    {
        $this->renameTable('project', 'conf');

        echo "m151005_001053_alter_conf_2_project is reverted.\n";

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
