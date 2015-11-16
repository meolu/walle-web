<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "group".
 *
 * @property integer $id
 * @property integer $project_id
 * @property string $user_id
 */
class Group extends \yii\db\ActiveRecord
{
    /**
     * 普通开发者
     */
    const TYPE_USER  = 0;

    /**
     * 管理员
     */
    const TYPE_ADMIN = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'user_id'], 'required'],
            [['project_id', 'user_id', 'type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'project_id' => 'Project ID',
            'user_id'    => 'User ID',
            'type'       => 'Type',
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
     * 项目添加用户
     *
     * @param $projectId
     * @param $userId array
     * @return bool
     */
    public static function addGroupUser($projectId, $userIds, $type = Group::TYPE_USER) {
        // 是否已在组内
        $exitsUids = Group::find()
            ->select(['user_id'])
            ->where(['project_id' => $projectId, 'user_id' => $userIds])
            ->column();
        $notExists = array_diff($userIds, $exitsUids);
        if (empty($notExists)) return true;

        $group = new Group();
        foreach ($notExists as $uid) {
            $relation = clone $group;
            $relation->attributes = [
                'project_id' => $projectId,
                'user_id'    => $uid,
                'type'       => $type,
            ];
            $relation->save();
        }
        return true;
    }

    /**
     * 是否为该项目的审核管理员
     *
     * @param $projectId
     * @param $uid
     * @return int|string
     */
    public static function isAuditAdmin($uid, $projectId = null) {
        $isAuditAdmin = static::find()
            ->where(['user_id' => $uid, 'type' => Group::TYPE_ADMIN]);
        if ($projectId) {
            $isAuditAdmin->andWhere(['project_id' => $projectId, ]);
        }
        return $isAuditAdmin->count();
    }

    /**
     * 获取用户可以审核的项目
     *
     * @param $uid
     * @return array
     */
    public static function getAuditProjectIds($uid) {
        return static::find()
            ->select(['project_id'])
            ->where(['user_id' => $uid, 'type' => Group::TYPE_ADMIN])
            ->column();
    }

}
