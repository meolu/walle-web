<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "conf".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $conf
 * @property integer $level
 * @property integer $status
 * @property integer $at
 */
class Conf extends \yii\db\ActiveRecord
{
    const CONF_TPL = 'conf_tpl';
    // 有效状态
    const STATUS_VALID = 1;

    // 测试环境
    const LEVEL_TEST  = 1;

    // 模拟线上环境
    const LEVEL_SIMU  = 2;

    // 线上环境
    const LEVEL_PROD  = 3;

    public static $LEVEL = [
        self::LEVEL_TEST => 'test',
        self::LEVEL_SIMU => 'simu',
        self::LEVEL_PROD => 'prod',
    ];

    public $context;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'conf';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'conf', 'level', 'created_at'], 'required'],
            [['user_id', 'level', 'status', 'created_at', 'level'], 'integer'],
            [['name', 'conf'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'user_id',
            'name' => 'Name',
            'conf' => 'Conf',
            'level' => 'Level',
            'status' => 'Status',
            'created_at' => 'created_at',
        ];
    }

    public static function getConfigFile($id) {
        $conf = static::findOne($id);
        if (!$conf) {
            throw new \Exception('找不到此任务号');
        };
        return static::getConf($conf->conf);
    }

    public static function getConf($name) {
        return sprintf("%s/%s/%s.yml",
            rtrim(\Yii::$app->basePath, '/'), trim(Yii::$app->params['config.dir'], '/'), $name);
    }

    public static function saveConfContext($name, $context) {
        $file = static::getConf($name);
        return file_put_contents($file, $context);
    }

    public static function getConfContext($name) {
        $file = static::getConf($name);
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return '';
    }
}
