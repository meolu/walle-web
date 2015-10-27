<?php

use yii\db\Migration;
use \app\models\Project;
use \yii\db\mssql\Schema;

class m151027_063246_keep_version_num extends Migration
{
    public function up()
    {
        $this->addColumn(Project::tableName(), 'keep_version_num', Schema::TYPE_INTEGER . '(3) NOT NULL DEFAULT 20 COMMENT "线上版本保留数" AFTER audit');

    }

    public function down()
    {
        $this->dropColumn(Project::tableName(), 'keep_version_num');
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
