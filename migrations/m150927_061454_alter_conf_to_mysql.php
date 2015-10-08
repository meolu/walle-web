<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Project;
use app\models\Task;

class m150927_061454_alter_conf_to_mysql extends Migration
{
    public function up()
    {
        $this->dropColumn('conf', 'conf');
        $this->addColumn('conf', 'git_url', Schema::TYPE_STRING . '(200) DEFAULT "" COMMENT "git地址"');
        $this->addColumn('conf', 'deploy_from', Schema::TYPE_STRING . '(200) NOT NULL COMMENT "宿主机存放clone出来的文件"');
        $this->addColumn('conf', 'excludes', Schema::TYPE_STRING . '(500) DEFAULT "" COMMENT "要排除的文件"');
        $this->addColumn('conf', 'release_user', Schema::TYPE_STRING . '(50) NOT NULL COMMENT "目标机器用户"');
        $this->addColumn('conf', 'release_to', Schema::TYPE_STRING . '(200) NOT NULL COMMENT "目标机器的目录，相当于nginx的root，可直接web访问"');
        $this->addColumn('conf', 'release_library', Schema::TYPE_STRING . '(200) NOT NULL COMMENT "目标机器版本发布库"');
        $this->addColumn('conf', 'hosts', Schema::TYPE_STRING . '(500) NOT NULL COMMENT "目标机器列表"');
        $this->addColumn('conf', 'pre_deploy', Schema::TYPE_STRING . '(500) DEFAULT "" COMMENT "部署前置任务"');
        $this->addColumn('conf', 'post_deploy', Schema::TYPE_STRING . '(500) DEFAULT "" COMMENT "同步之前任务"');
        $this->addColumn('conf', 'post_release', Schema::TYPE_STRING . '(500) DEFAULT "" COMMENT "同步之后任务"');
        $this->addColumn('conf', 'git_type', Schema::TYPE_STRING . '(50) DEFAULT "branch" COMMENT "两种上线方式，分支、tag"');
        $this->addColumn('conf', 'audit', Schema::TYPE_SMALLINT . '(1) DEFAULT 0 COMMENT "是否需要审核任务0不需要，1需要"');
        $this->dropColumn('conf', 'created_at');
        $this->addColumn('conf', 'created_at', Schema::TYPE_DATETIME . ' COMMENT "创建时间" after audit');
        $this->addColumn('conf', 'updated_at', Schema::TYPE_DATETIME . ' COMMENT "修改时间"');
        $this->dropColumn(Task::tableName(), 'created_at');
        $this->addColumn(Task::tableName(), 'created_at', Schema::TYPE_DATETIME . ' COMMENT "创建时间"');
        $this->addColumn(Task::tableName(), 'updated_at', Schema::TYPE_DATETIME . ' COMMENT "修改时间"');

    }

    public function down()
    {
        echo "m150927_061454_alter_conf_to_mysql cannot be reverted.\n";

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
