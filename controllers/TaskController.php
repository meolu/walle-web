<?php

namespace app\controllers;

use yii;
use yii\data\Pagination;
use walle\command\Command;
use walle\command\Folder;
use walle\command\Git;
use walle\command\Task as WalleTask;
use app\components\Controller;
use app\models\Task;
use app\models\Record;
use app\models\Conf;
use app\models\User;

class TaskController extends Controller {
    
    protected $task;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action) {
        return parent::beforeAction($action);
    }

    public function actionIndex($page = 1, $size = 10) {
        $size = $this->getParam('per-page') ?: $size;
        $user = User::findOne(\Yii::$app->user->id);
        $list = Task::find()
            ->with('user')
            ->with('conf');
        if ($user->role != User::ROLE_ADMIN) {
            $list->where(['user_id' => \Yii::$app->user->id]);
        }

        $kw = \Yii::$app->request->post('kw');
        if ($kw) {
            $list->andWhere(['or', "commit_id like '%" . $kw . "%'", "title like '%" . $kw . "%'"]);
        }
        $tasks = $list->orderBy('id desc');
        $list = $tasks->offset(($page - 1) * $size)->limit(10)
            ->asArray()->all();

        $view = $user->role == User::ROLE_ADMIN ? 'admin-list' : 'dev-list';
        $pages = new Pagination(['totalCount' => $tasks->count(), 'pageSize' => 10]);
        return $this->render($view, [
            'list'  => $list,
            'pages' => $pages,
        ]);
    }


    /**
     * 提交任务
     *
     * @param $projectId
     * @return string
     */
    public function actionSubmit($projectId = null) {
        $task = new Task();
        if ($projectId) {
            $conf = Conf::findOne($projectId);
        }
        if (\Yii::$app->request->getIsPost() && $task->load(\Yii::$app->request->post())) {
            // 是否需要审核
            $status = $conf->audit == Conf::AUDIT_YES ? Task::STATUS_SUBMIT : Task::STATUS_PASS;
            $task->user_id = \Yii::$app->user->id;
            $task->project_id = $projectId;
            $task->status = $status;
            if ($task->save()) {
                $this->redirect('/task/');
            }
        }
        if ($projectId) {
            return $this->render('submit', [
                'task' => $task,
                'conf' => $conf,
            ]);
        }
        $projects = Conf::find()
            ->where(['status' => Conf::STATUS_VALID])
            ->asArray()->all();
        return $this->render('select-project', [
            'projects' => $projects,
        ]);
    }


    /**
     * 任务删除
     *
     * @return string
     * @throws \Exception
     */
    public function actionDelete($taskId) {
        $task = Task::findOne($taskId);
        if (!$task) {
            throw new \Exception('任务号不存在：）');
        }
        if ($task->user_id != \Yii::$app->user->id) {
            throw new \Exception('不可以操作其它人的任务：）');
        }
        if ($task->status == Task::STATUS_DONE) {
            throw new \Exception('不可以删除已上线成功的任务：）');
        }
        if (!$task->delete()) throw new \Exception('删除失败');
        $this->renderJson([]);

    }

    /**
     * 生成回滚任务
     *
     * @return string
     * @throws \Exception
     */
    public function actionRollback($taskId) {
        $this->task = Task::findOne($taskId);
        if (!$this->task) {
            throw new \Exception('任务号不存在：）');
        }
        if ($this->task->user_id != \Yii::$app->user->id) {
            throw new \Exception('不可以操作其它人的任务：）');
        }
        if ($this->task->ex_link_id == $this->task->link_id) {
            throw new \Exception('已回滚的任务不能再次回滚：（');
        }
        $rollbackTask = new Task();
        $conf = Conf::findOne($this->task->project_id);
        // 是否需要审核
        $status = $conf->audit == Conf::AUDIT_YES ? Task::STATUS_SUBMIT : Task::STATUS_PASS;
        $rollbackTask->attributes = [
            'user_id' => \Yii::$app->user->id,
            'project_id' => $this->task->project_id,
            'status' => $status,
            'action' => Task::ACTION_ROLLBACK,
            'link_id' => $this->task->ex_link_id,
            'ex_link_id' => $this->task->ex_link_id,
            'title' => $this->task->title . ' - 回滚',
            'commit_id' => $this->task->commit_id,
        ];
        if ($rollbackTask->save()) {
            $url = $conf->audit == Conf::AUDIT_YES
                ? '/task/'
                : '/walle/deploy?taskId=' . $rollbackTask->id;
            $this->renderJson([
                'url' => $url,
            ]);
        } else {
            $this->renderJson([], -1, '生成回滚任务失败');
        }
    }


    /**
     * 任务审核
     *
     * @param $id
     * @param $operation
     */
    public function actionTaskOperation($id, $operation) {
        $task = Task::findOne($id);
        if (!$task) {
            static::renderJson([], -1, '任务号不存在');
        }
        $task->status = $operation ? Task::STATUS_PASS : Task::STATUS_REFUSE;
        $task->save();
        static::renderJson(['status' => \Yii::t('status', 'task_status_' . $task->status)]);
    }

}
