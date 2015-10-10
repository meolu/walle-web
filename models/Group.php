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
            [['project_id'], 'integer'],
            [['user_id'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'user_id' => 'User ID',
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
     * @param $userId
     * @return bool
     */
    public static function addGroupUser($projectId, $userIds) {
        // 是否已在组内
        $exitsUids = Group::find()
            ->select(['user_id'])
            ->where(['project_id' => $projectId, 'user_id' => $userIds])
            ->column();
        $notExists = array_diff($userIds, $exitsUids);
        if (!$notExists) return true;

        $group = new Group();
        foreach ($notExists as $uid) {
            $relation = clone $group;
            $relation->attributes = [
                'project_id' => $projectId,
                'user_id'    => $uid,
            ];
            $relation->save();
        }
        return true;
    }

}
