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
class DynamicConf extends \yii\db\ActiveRecord
{
    const K_ONLINE_VERSION = 'online_version';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dynamic_conf';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['name', 'value'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'value' => 'value',
        ];
    }
}
