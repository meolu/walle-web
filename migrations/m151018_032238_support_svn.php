<?php

use yii\db\Migration;
use \yii\db\mysql\Schema;
use app\models\Project;
use app\models\Task;

class m151018_032238_support_svn extends Migration
{
    /**
     * 开始支持svn版本管理
     */
    public function up()
    {
        $this->renameColumn(Project::tableName(), 'git_url', 'repo_url');
        $this->renameColumn(Project::tableName(), 'git_type', 'repo_mode');
        $this->alterColumn(Project::tableName(), 'repo_mode', Schema::TYPE_STRING . '(50) DEFAULT "branch" COMMENT "上线方式：branch/tag" AFTER repo_url');
        $this->addColumn(Project::tableName(), 'repo_type', Schema::TYPE_STRING . '(10) DEFAULT "git" COMMENT "上线方式：git/svn" AFTER repo_mode');
        $this->addColumn(Project::tableName(), 'repo_username', Schema::TYPE_STRING . '(50) DEFAULT "" COMMENT "版本管理系统的用户名，一般为svn的用户名" AFTER repo_url');
        $this->addColumn(Project::tableName(), 'repo_password', Schema::TYPE_STRING . '(100) DEFAULT "" COMMENT "版本管理系统的密码，一般为svn的密码" AFTER repo_username');
        $this->addColumn(Task::tableName(), 'file_list', Schema::TYPE_TEXT . ' COMMENT "文件列表，svn上线方式可能会产生"');
        $this->alterColumn(Task::tableName(), 'commit_id', Schema::TYPE_STRING . '(100) DEFAULT "" COMMENT "git commit id"');
    }

    public function down()
    {
        $this->dropColumn(Project::tableName(), 'repo_username');
        $this->dropColumn(Project::tableName(), 'repo_password');
        $this->renameColumn(Project::tableName(), 'repo_url', 'git_url');
        $this->renameColumn(Project::tableName(), 'repo_mode', 'git_type');
        $this->dropColumn(Project::tableName(), 'repo_type');
        $this->dropColumn(Task::tableName(), 'file_list');
        echo "m151018_032238_support_svn be reverted.\n";

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
