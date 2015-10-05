<?php

use yii\db\Schema;
use yii\db\Migration;

class m150929_034627_session_to_mysql extends Migration
{
    public function up()
    {
        $this->createTable('session', [
            'id' => Schema::TYPE_STRING . '(40) NOT NULL PRIMARY KEY',
            'expire' => Schema::TYPE_INTEGER . '(10) unsigned default null',
            'data' => 'blob',
        ]);
    }

    public function down()
    {
        $this->dropTable('session');
        echo "m150929_034627_session_to_mysql is reverted.\n";

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
