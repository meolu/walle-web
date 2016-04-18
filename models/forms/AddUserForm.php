<?php

namespace app\models\forms;

use yii;
use yii\base\Model;
use app\models\User;
use app\models\queries\UserQuery;

class AddUserForm extends Model {

    public $email;
    public $password;
    public $realname;
    public $role;

    public function attributeLabels()
    {
        return [
            'email' => '用户名',
            'password' => '密码',
            'realname' => '姓名',
            'role' => '角色',
        ];
    }

    public function rules() {
        return [
            [['email',  'password', 'realname', 'role'], 'required'],

            ['email', 'email'],

            ['password', 'string', 'min' => 6, 'max' => 30],

            ['realname', 'string', 'min' => 2],
            ['role', 'in', 'range' => [User::ROLE_DEV, User::ROLE_ADMIN]],
        ];
    }

    public function signup() {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->email;
            $user->email = $this->email;
            $user->role = $this->role;
            $user->realname = $this->realname;
            $user->setpassword($this->password);

            // 给默认头像
            $user->status = User::STATUS_ACTIVE;
            $user->avatar = 'default.jpg';

            if ($user->save()) {
                return $user;
            }

            return null;
        }
    }
}