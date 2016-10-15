<?php

namespace app\controllers;

use yii;
use yii\web\NotFoundHttpException;
use yii\data\Pagination;
use app\components\Controller;
use app\models\Project;
use app\models\User;
use app\models\Group;
use app\components\GlobalHelper;

class ConfController extends Controller
{

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action) {
        parent::beforeAction($action);
        if (!GlobalHelper::isValidAdmin()) {
            throw new \Exception(yii::t('conf', 'you are not active'));
        }
        return true;
    }

    /**
     * 配置项目列表
     *
     */
    public function actionIndex() {

        // 为了方便用户更改表名，避免表名直接定死
        $groupTable   = Group::tableName();
        $projectTable = Project::tableName();
        // 显示该用户为管理员的所有项目
        $project = Project::find()
            ->leftJoin(Group::tableName(), "`$groupTable`.`project_id`=`$projectTable`.`id`")
            ->where(["`$groupTable`.`user_id`" => $this->uid, "`$groupTable`.`type`" => Group::TYPE_ADMIN]); 

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
        $project = $this->findModel($projectId);

        return $this->render('preview', [
            'conf' => $project,
        ]);
    }

    /**
     * 项目配置检测
     *
     * @param $projectId
     * @return string
     * @throws \Exception
     */
    public function actionDetection($projectId) {
        $this->layout = 'modal';
        $project = $this->findModel($projectId);
        return $this->render('detection', [
            'project' => $project,
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
        $project = $this->findModel($projectId);
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
        if ($projectId) {
            $project = $this->findModel($projectId);
        } else {
            $project = new Project();
            $project->loadDefaultValues();
        }

        if (\Yii::$app->request->getIsPost() && $project->load(Yii::$app->request->post())) {
            $project->user_id = $this->uid;

            if ($project->save()) {
                // 保存ansible需要的hosts文件
                $this->_saveAnsibleHosts($project);
                $this->redirect('@web/conf/');
            }
        }

        return $this->render('edit', [
            'conf' => $project,
        ]);
    }

    /**
     * 复制项目配置
     *
     * @return string
     * @throws \Exception
     */
    public function actionCopy($projectId) {
        $project = $this->findModel($projectId);
        // 复制为新项目
        $project->name .= ' - copy';
        $copy = new Project();
        $copy->load($project->getAttributes(), '');

        if (!$copy->save()) throw new \Exception(yii::t('conf', 'copy failed'));

        // 删除ansible配置文件
        if ($project->ansible) {
            copy(Project::getAnsibleHostsFile($project->id), Project::getAnsibleHostsFile($copy->id));
        }

        $this->renderJson([]);
    }

    /**
     * 删除配置
     *
     * @return string
     * @throws \Exception
     */
    public function actionDelete($projectId) {
        $project = $this->findModel($projectId);

        if (!$project->delete()) throw new \Exception(yii::t('w', 'delete failed'));

        // 删除ansible配置文件
        if ($project->ansible) {
            unlink(Project::getAnsibleHostsFile($project->id));
        }

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
            throw new \Exception(yii::t('conf', 'relation not exists'));
        }
        $project = $this->findModel($group->project_id);

        if (!$group->delete()) throw new \Exception(yii::t('w', 'delete failed'));
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
            throw new \Exception(yii::t('conf', 'relation not exists'));
        }
        $project = $this->findModel($group->project_id);
        if (!in_array($type, [Group::TYPE_ADMIN, Group::TYPE_USER])) {
            throw new \Exception(yii::t('conf', 'unknown relation type'));
        }
        $group->type = (int)$type;
        if (!$group->save()) throw new \Exception(yii::t('w', 'update failed'));
        $this->renderJson([]);
    }

    /**
     * 简化
     *
     * @param integer $id
     * @return the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Project::getConf($id)) !== null) {
            //判断是否为管理员
            if(!Group::isAuditAdmin($this->uid, $model->id)){
                throw new \Exception(yii::t('w', 'you are not admin of project'));
            }
            return $model;
        } else {
            throw new NotFoundHttpException(yii::t('conf', 'project not exists'));
        }
    }

    /**
     * @param Project $project
     * @return bool
     * @throws \Exception
     */
    protected function _saveAnsibleHosts(Project $project) {

        if (!$project->ansible) {
            // 未开启ansible, 不用保存
            return true;
        }

        $filePath = Project::getAnsibleHostsFile($project->id);
        $ret = @file_put_contents($filePath, $project->hosts);
        if (!$ret) {
            throw new \Exception(yii::t('conf', 'ansible hosts save error', ['path' => $filePath]));
        }

        return true;
    }

}
