<?php

namespace app\models;

use Yii;
use app\components\Command;

/**
 * This is the model class for table "record".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $task_id
 * @property integer $status
 * @property integer $action
 * @property integer $at
 * @property integer $duration
 * @property integer $memo
 */
class Record extends \yii\db\ActiveRecord
{

    /**
     * 服务器权限检查
     */
    const ACTION_PERMSSION = 10;

    /**
     * 部署前置触发任务
     */
    const ACTION_PRE_DEPLOY = 15;

    /**
     * 本地代码更新
     */
    const ACTION_CLONE = 30;

    /**
     * 部署后置触发任务
     */
    const ACTION_POST_DEPLOY = 50;

    /**
     * 同步代码到服务器
     */
    const ACTION_SYNC  = 60;

    /**
     * 同步完所有目标机器时触发任务
     */
    const ACTION_POST_RELEASE = 70;

    /**
     * 软链接，最后一个得是100
     */
    const ACTION_LINK  = 100;


    public static $ACTION_PERCENT = [
        self::ACTION_PERMSSION => '25',
        self::ACTION_CLONE => '50',
        self::ACTION_SYNC => '75',
        self::ACTION_LINK => '100',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'task_id', 'status'], 'required'],
            [['user_id', 'task_id', 'status', 'created_at', 'duration', 'action'], 'integer'],
            [['memo', 'command'], 'string'],
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
            'task_id' => 'task_id',
            'status' => 'Status',
            'created_at' => 'created_at',
            'action' => 'action',
            'duration' => 'duration',
            'memo' => 'memo',
        ];
    }

    /**
     * 保存记录
     *
     * @param Command $commandObj
     * @param $task_id
     * @param $action
     * @param $duration
     * @return mixed
     */
    public static function saveRecord(Command $commandObj, $task_id, $action, $duration) {
        $record = new static();
        $record->attributes = [
            'user_id'    => \Yii::$app->user->id,
            'task_id'    => $task_id,
            'status'     => (int)$commandObj->getExeStatus(),
            'action'     => $action,
            'created_at' => time(),
            'command'    => var_export($commandObj->getExeCommand(), true),
            'memo'       => var_export($commandObj->getExeLog(), true),
            'duration'   => $duration,
        ];
        return $record->save();
    }
}
