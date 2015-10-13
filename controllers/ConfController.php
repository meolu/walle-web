<?php

namespace app\controllers;

use yii;
use yii\data\Pagination;
use app\components\Controller;
use app\models\Project;
use app\models\User;
use app\models\Group;

class ConfController extends Controller
{

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action) {
        parent::beforeAction($action);
        if (\Yii::$app->user->identity->role != User::ROLE_ADMIN) {
            throw new \Exception('非管理员不能操作：（');
        }
        return true;
    }

    /**
     * 配置项目列表
     *
     */
    public function actionIndex() {
        $project = Project::find()
            ->where(['user_id' => $this->uid]);
        $kw = \Yii::$app->request->post('kw');
        if ($kw) {
            $project->andWhere(['like', "name", $kw]);
        }
        $project = $project->asArray()->all();
        return $this->render('index', [
            'list' => $project,
        ]);
    }

    /**
     * 配置项目
     *
     * @param $projectId
     * @return string
     * @throws \Exception
     */
    public function actionPreview($projectId) {
        $this->layout = 'modal';
        $project = Project::findOne($projectId);
        if (!$project) throw new \Exception('找不到项目');

        return $this->render('preview', [
            'conf' => $project,
        ]);
    }

    /**
     * 配置项目
     *
     * @param $projectId
     * @return string
     * @throws \Exception
     */
    public function actionGroup($projectId) {
        // 配置信息
        $project = Project::findOne($projectId);
        if (!$project) {
            throw new \Exception('项目不存在：）');
        }
        if ($project->user_id != $this->uid) {
            throw new \Exception('不可以操作其它人的项目：）');
        }
        // 添加用户
        if (\Yii::$app->request->getIsPost() && \Yii::$app->request->post('user')) {
            Group::addGroupUser($projectId, \Yii::$app->request->post('user'));
        }
        // 项目的分组用户
        $group = Group::find()
            ->with('user')
            ->where(['project_id' => $projectId])
            ->indexBy('user_id')
            ->orderBy(['type' => SORT_DESC])
            ->asArray()->all();
        // 所有用户
        $users = User::find()
            ->select(['id', 'email', 'realname'])
            ->where(['is_email_verified' => 1])
            ->asArray()->all();

        return $this->render('group', [
            'conf'  => $project,
            'users' => $users,
            'group' => $group,
        ]);
    }

    /**
     * 配置项目
     *
     * @param $projectId
     * @return string
     * @throws \Exception
     */
    public function actionEdit($projectId = null) {
        $project = $projectId ? Project::findOne($projectId) : new Project();

        if ($projectId && !$project) {
            throw new \Exception('找不到项目配置');
        }

        if ($projectId && $this->uid != $project->user_id) {
            throw new \Exception('非项目创建人，不可修改');
        }

        if (\Yii::$app->request->getIsPost() && $project->load(Yii::$app->request->post())) {
            $project->user_id = $this->uid;
            if ($project->save()) {
                $this->redirect('/conf/');
            }
        }

        return $this->render('edit', [
            'conf' => $project,
        ]);
    }

    /**
     * 删除配置
     *
     * @return string
     * @throws \Exception
     */
    public function actionDelete($projectId) {
        $project = Project::findOne($projectId);
        if (!$project) {
            throw new \Exception('项目不存在：）');
        }
        if ($project->user_id != $this->uid) {
            throw new \Exception('不可以操作其它人的项目：）');
        }
        if (!$project->delete()) throw new \Exception('删除失败');
        $this->renderJson([]);
    }

    /**
     * 删除项目的用户关系
     *
     * @return string
     * @throws \Exception
     */
    public function actionDeleteRelation($id) {
        $group = Group::findOne($id);
        if (!$group) {
            throw new \Exception('关系不存在：）');
        }
        $project = Project::findOne($group->project_id);
        if ($project->user_id != $this->uid) {
            throw new \Exception('不可以操作其它人的项目：）');
        }

        if (!$group->delete()) throw new \Exception('删除失败');
        $this->renderJson([]);
    }

    /**
     * 项目审核管理员设置
     *
     * @return string
     * @throws \Exception
     */
    public function actionEditRelation($id, $type = 0) {
        $group = Group::findOne($id);
        if (!$group) {
            throw new \Exception('关系不存在：）');
        }
        $project = Project::findOne($group->project_id);
        if ($project->user_id != $this->uid) {
            throw new \Exception('不可以操作其它人的项目：）');
        }
        if (!in_array($type, [Group::TYPE_ADMIN, Group::TYPE_USER])) {
            throw new \Exception('未知的关系类型：）');
        }
        $group->type = (int)$type;
        if (!$group->save()) throw new \Exception('更新失败');
        $this->renderJson([]);
    }
}
