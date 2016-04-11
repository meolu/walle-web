<?php

use app\models\Project;
use yii\db\Migration;
use yii\db\mssql\Schema;

class m160307_082032_ansible extends Migration
{
    public function up()
    {
        $this->addColumn(Project::tableName(), 'ansible', Schema::TYPE_SMALLINT . '(3) NOT NULL DEFAULT 0 COMMENT "是否启用Ansible 0关闭，1开启" AFTER audit');

    }

    public function down()
    {
        $this->dropColumn(Project::tableName(), 'ansible');
        echo "m160307_082032_ansible was reverted.\n";

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
