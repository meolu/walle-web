<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\models\Project;

class m160127_132028_project_web_domain extends Migration
{
    public function up()
    {
		$this->addColumn(Project::tableName(), 'web_root_domain', Schema::TYPE_STRING . '(200) NOT NULL DEFAULT \'\' COMMENT "web根域名，用于拼接前台预览完整域名" AFTER repo_url');
    }

    public function down()
    {
    	$this->dropColumn(Project::tableName(), 'web_root_domain');
        echo "m160127_132028_project_web_domain was reverted.\n";

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
