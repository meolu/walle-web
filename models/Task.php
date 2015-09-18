<?php

namespace app\models;

use app\models\User;
use Yii;
use app\models\Conf;
/**
 * This is the model class for table "task".
 *
 * @property integer $id
 * @property string $userid
 * @property integer $status
 * @property integer $at
 * @property string $title
 * @property string $commitid
 */
class Task extends \yii\db\ActiveRecord
{
    const ACTION_ONLINE = 0;

    const ACTION_ROLLBACK = 1;
    /**
     * 任务新提交
     */
    const STATUS_SUBMIT = 0;
    /**
     * 任务通过
     */
    const STATUS_PASS   = 1;
    /**
     * 任务拒绝
     */
    const STATUS_REFUSE = 2;
    /**
     * 任务上线完成
     */
    const STATUS_DONE   = 3;
    /**
     * 任务上线失败
     */
    const STATUS_FAILED = 4;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'created_at', 'title', 'commit_id', 'project_id'], 'required'],
            [['user_id', 'status', 'created_at', 'project_id', 'action'], 'integer'],
            [['title', 'commit_id', 'link_id', 'ex_link_id'], 'string', 'max' => 100],
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
            'status' => 'Status',
            'created_at' => 'created_at',
            'title' => 'Title',
            'commit_id' => 'commit_id',
            'ex_link_id' => 'ex_link_id',
        ];
    }

    /**
     * width('user')
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * with('conf')
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConf() {
        return $this->hasOne(Conf::className(), ['id' => 'project_id']);
    }
}
