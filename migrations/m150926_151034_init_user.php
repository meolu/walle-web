<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\User;

class m150926_151034_init_user extends Migration
{
    public function up()
    {
        // 初始化一个管理员
        $this->insert(User::tableName(), [
            'id' => 1,
            'username' => 'admin',
            'is_email_verified' => 1,
            'auth_key' => 'cJIrTa_b2Hnjn6BZkrL8PJkYto2Ael3O',
            'password_hash' => '$2y$13$PB5IFQ9IEvuvDmSnUsPErOKT3NZ.xEGNLg3aTTJRq0zycv/XO0wUW',
            'password_reset_token' => NULL,
            'email_confirmation_token' => 'UpToOIawm1L8GjN6pLO4r-1oj20nLT5f_1443280741',
            'email' => 'admin@xxx.com',
            'role' => 1,
            'status' => 10,
            'created_at' => '2015-09-26 21:20:32',
            'updated_at' => '2015-09-26 21:20:32',
            'realname' => '管理员',
        ]);

        // 初始化一个开发者用户
        $this->insert(User::tableName(), [
            'id' => 2,
            'username' => 'demo',
            'is_email_verified' => 1,
            'auth_key' => 'RpFh1J9E0MrGY31e_Z7GIh3EkC6hS0aa',
            'password_hash' => '$2y$13$YoqhrkWcr1ZXADOSkj4S..jUAWlIrXdfcP00STqEMpF1d1b85SU7a',
            'password_reset_token' => NULL,
            'email_confirmation_token' => 'YnR4Z6bfK3fle7QP_t6wcnB5zSP__nkz_1443280906',
            'email' => 'admin@xxx.com',
            'role' => 2,
            'status' => 10,
            'created_at' => '2015-09-26 21:20:32',
            'updated_at' => '2015-09-26 21:20:32',
            'realname' => 'demo',
        ]);

        return true;
    }

    public function down()
    {
        echo "m150926_151034_init_user reverted.\n";
        $this->delete(User::tableName(), ['id' => [1, 2]]);

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
