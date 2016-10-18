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
    const STAGE_PERMISSION = 'stage_permission';

    /**
     * 部署前置触发任务
     */
    const STAGE_PRE_DEPLOY = 'stage_pre_deply';

    /**
     * 本地代码更新
     */
    const STAGE_CHECK_OUT = 'stage_check_out';

    /**
     * 部署后置触发任务
     */
    const STAGE_POST_DEPLOY = 'stage_post_deploy';

    /**
     * 同步代码到服务器
     */
    const STAGE_SYNC  = 'stage_sync';

    /**
     * 更新完所有目标机器时触发任务
     */
    const STAGE_UPDATE_REMOTE = 'stage_update_remote';

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
            [['user_id', 'task_id', 'status', 'created_at', 'duration'], 'integer'],
            [['memo', 'command', 'action', 'execute_at', 'server_user', 'server_name', 'server_directory', ], 'string'],
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
    public static function saveRecord(Command $commandObj, $task_id, $action) {
        $record = new static();
        $record->attributes = [
            'user_id'    => \Yii::$app->user->id,
            'task_id'    => $task_id,
            'status'     => (int)$commandObj->getExeStatus(),
            'action'     => $action,
            'execute_at' => $commandObj->getStartTime(),
            'server_user'     => 'work', // todo
            'server_name'     => '192.168.0.1', //todo
            'server_directory' => '/home/www/marketing/', // todo
            'command'    => var_export($commandObj->getExeCommand(), true),
            'memo'       => substr(var_export($commandObj->getExeLog(), true), 0, 65530),
            'duration'   => $commandObj->getDuration(),
        ];
        return $record->save();
    }

}
