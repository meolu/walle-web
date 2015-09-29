<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\models\Record;

class m150929_004629_change_record_action extends Migration
{
    public function up()
    {
        $this->alterColumn(Record::tableName(), 'action', Schema::TYPE_INTEGER . '(3) unsigned DEFAULT 10 COMMENT "任务执行到的阶段"');

    }

    public function down()
    {
        echo "m150929_004629_change_record_action cannot be reverted.\n";

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
