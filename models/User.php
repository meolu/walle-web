<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\models\behaviors\TimestampBehavior;
use app\models\queries\UserQuery;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $is_email_verified
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email_confirmation_token
 * @property string $email
 * @property string $avatar
 * @property string $auth_key
 * @property integer $role
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{

    // 邮件未激活
    const MAIL_INACTIVE = 0;

    // 邮件激活
    const MAIL_ACTIVE = 1;

    /**
     * status状态表
     *
     * developer：注册 1 =》 激活 1
     * admin：注册 1 =》 激活 1 =》 其它admin认证 2
     */

    // 冻结
    const STATUS_INVALID  = 0;

    // 注册后默认状态（激活后即可用）或 其它admin认证前的状态
    const STATUS_ACTIVE = 1;

    // 管理员审核通过
    const STATUS_ADMIN_ACTIVE   = 2;

    /**
     * 开发者
     */
    const ROLE_DEV   = 1;

    /**
     * 管理员
     */
    const ROLE_ADMIN = 2;

    /**
     * 头像目录
     */
    const AVATAR_ROOT = '/dist/avatars/';

    /**
     * @var string|null the current password value from form input
     */
    protected $_password;

    /**
     * @return UserQuery custom query class with user scopes
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            'signup' => ['username','email','password','role'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'email' => '邮箱',
            'password' => '密码',
            'role' => '角色',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username','email','password','role'], 'required', 'on'=>'signup'],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ADMIN_ACTIVE, self::STATUS_ACTIVE, self::STATUS_INVALID]],

            ['role', 'default', 'value' => self::ROLE_DEV],
            ['role', 'in', 'range' => [self::ROLE_DEV, self::ROLE_ADMIN]],

            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'unique'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            [['avatar', 'realname'], 'string'],
            [['email', 'avatar'], 'filter', 'filter' => 'trim'],
            ['email', 'validateEmail', 'on'=>'signup'],
            ['email', 'email', 'on'=>'signup'],
            ['email', 'unique', 'on'=>'signup'],
        ];
    }

    public function validateEmail($attribute, $params) {
        // 支持多邮箱绑定
        $mailSuffix = join('|@', \Yii::$app->params['mail-suffix']);
        if (!preg_match("/.*(@{$mailSuffix})$/", $this->$attribute)) {
            $this->addError($attribute, "没有" . join('，', \Yii::$app->params['mail-suffix']) . "邮箱不可注册：）");
        }
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->generateAuthKey();
            $this->generateEmailConfirmationToken();
            // 名字与邮箱
            if (!$this->realname) {
                $this->realname = $this->username;
            }
            // 本地注册需要把 username = email
            $userDriver = isset(\Yii::$app->params['user_driver']) == true && empty(\Yii::$app->params['user_driver']) == false ? \Yii::$app->params['user_driver'] : 'local';
            if ($userDriver == 'local') {
                $this->username = $this->email;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findByUsername($username)
    {
        return static::findOne([
                'username' => $username
            ]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->realname;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->_password = $password;
        if (!empty($password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        }
    }

    /**
     * @return string|null the current password value, if set from form. Null otherwise.
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new email confirmation token
     */
    public function generateEmailConfirmationToken()
    {
        $this->email_confirmation_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     * @param bool $save whether to save the record. Default is `false`.
     * @return bool|null whether the save was successful or null if $save was false.
     */
    public function removePasswordResetToken($save = false)
    {
        $this->password_reset_token = null;
        if ($save) {
            return $this->save();
        }
    }

    /**
     * Removes email confirmation token and sets is_email_verified to true
     * @param bool $save whether to save the record. Default is `false`.
     * @return bool|null whether the save was successful or null if $save was false.
     */
    public function removeEmailConfirmationToken($save = false)
    {
        $this->email_confirmation_token = null;
        $this->is_email_verified = 1;
        if ($save) {
            return $this->save();
        }
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getInactiveAdminList() {
        return static::find()
            ->where(['is_email_verified' => static::MAIL_ACTIVE, 'role' => static::ROLE_ADMIN, 'status' => static::STATUS_ACTIVE])
            ->asArray()->all();
    }
}
