<?php
/**
 * 统一整理user.status的状态表
 */
use yii\db\Schema;
use yii\db\Migration;
use app\models\User;

class m160418_035413_user_status_migrate extends Migration
{
    public function up()
    {
        $this->alterColumn(User::tableName(), 'role', 'smallint(6) NOT NULL DEFAULT 1');
        $this->alterColumn(User::tableName(), 'status', 'smallint(6) NOT NULL DEFAULT 1');
        $this->update(User::tableName(), ['status' => User::STATUS_ADMIN_ACTIVE], 'status = 10 and role = 1');
        $this->update(User::tableName(), ['status' => User::STATUS_ACTIVE], 'status = 0 or (status = 10 and role > 1)');
        $this->update(User::tableName(), ['role' => 3], 'role=1');
        $this->update(User::tableName(), ['role' => User::ROLE_DEV], 'role=2');
        $this->update(User::tableName(), ['role' => User::ROLE_ADMIN], 'role=3');
    }

    public function down()
    {
        echo "m160418_035413_user_status_migrate cannot be reverted.\n";

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
