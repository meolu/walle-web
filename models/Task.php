<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "task".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $project_id
 * @property integer $action
 * @property integer $status
 * @property string $title
 * @property string $link_id
 * @property string $ex_link_id
 * @property string $commit_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Task extends \yii\db\ActiveRecord
{
    /**
     * 普通上线任务
     */
    const ACTION_ONLINE = 0;
    /**
     * 回滚任务
     */
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
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id', 'status', 'title', 'commit_id'], 'required'],
            [['user_id', 'project_id', 'action', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'link_id', 'ex_link_id', 'commit_id', 'branch'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'project_id' => 'Project ID',
            'action' => 'Action',
            'status' => 'Status',
            'title' => 'Title',
            'link_id' => 'Link ID',
            'ex_link_id' => 'Ex Link ID',
            'commit_id' => 'Commit ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * 是否能进行部署
     *
     * @param $status
     * @return bool
     */
    public static function canDeploy($status) {
        return in_array($status, [static::STATUS_PASS, static::STATUS_FAILED]);
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
     * with('project')
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProject() {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }
}
