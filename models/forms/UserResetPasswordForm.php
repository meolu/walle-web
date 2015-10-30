<?php
namespace app\models\forms;

use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use app\models\User;

/**
 * Password reset form
 */
class UserResetPasswordForm extends Model
{
    public $password;

    public $old_password;

    /**
     * @var \common\models\User
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param  string                          $token
     * @param  array                           $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($uid, $config = []) {
        $this->_user = User::findOne($uid);
        if (!$this->_user) {
            throw new InvalidParamException('找不到该用户.');
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', ], 'required'],
            [['password', ], 'string', 'min' => 6],
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        if (!empty($this->password)) {
            $this->_user->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        }
        return $this->_user->save();
    }
}
