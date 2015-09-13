<?php

namespace app\controllers;
use app\components\Controller;
use app\models\DynamicConf;
use walle\command\Git;
use walle\command\Sync;
use walle\command\Task as WalleTask;
use walle\command\RemoteCmd;
use walle\config\Config;
use app\models\Task;
use app\models\Record;
use walle\command\Command;
use app\models\Conf;
use app\models\User;

class WalleController extends Controller {

    private $_config;

    private $_task;

    public $enableCsrfValidation = false;

    public function actionIndex($kw = null) {
        $user = User::findOne(\Yii::$app->user->id);
        $list = Task::find()
            ->select(['username' => 'user.username', 'task.title', 'task.status', 'task.id', 'task.commit_id'])
            ->leftJoin('user', 'user.id=task.user_id');
        if ($user->role != User::ROLE_ADMIN) {
            $list->where(['user_id' => \Yii::$app->user->id]);
        }

        $kw = \Yii::$app->request->post('kw');
        if ($kw) {
            $list->andWhere(['or', "commit_id like '%" . $kw . "%'", "title like '%" . $kw . "%'"]);
        }
        $tasks = $list->orderBy('id desc')
            ->asArray()->all();

        $view = $user->role == User::ROLE_ADMIN ? 'admin-list' : 'dev-list';
        return $this->render($view, [
            'list' => $tasks,
        ]);
    }

    /**
     * 发起上线
     * @throws \Exception
     */
    public function actionStartDeploy() {
        $taskId = \Yii::$app->request->post('taskId');
        if (!$taskId) {
            static::renderJson([], -1, '任务号不能为空：）');
        }
        $this->_task = Task::findOne($taskId);
        if (!$this->_task) {
            throw new \Exception('任务号不存在：）');
        }
        if ($this->_task->user_id != \Yii::$app->user->id) {
            throw new \Exception('不可以操作其它人的任务：）');
        }
        // 任务失败或者审核通过时可发起上线
        if (!in_array($this->_task->status, [Task::STATUS_PASS, Task::STATUS_FAILED])) {
            throw new \Exception('任务不能被重复执行：）');
        }

        // yml配置
        $this->_config = new Config(Conf::getConfigFile($this->_task->project_id));
        // db配置
        $dbConf = Conf::findOne($this->_task->project_id);

        try {
            if ($this->_task->action == Task::ACTION_ONLINE) {
                $this->_checkPermission();
                $this->_gitUpdate();
                $this->_preDeploy();
                $this->_rsync();
                $this->_postRelease();
                $this->_link();
            } else {
                $this->_link($this->_task->ex_link_id);
            }


            $online = DynamicConf::findOne(DynamicConf::K_ONLINE_VERSION);
            if (!$online) {
                $online = new DynamicConf();
                $online->name = DynamicConf::K_ONLINE_VERSION;
            }
            // 记录此次上线的版本（软链号）和上线之前的版本
            /// 对于回滚的任务不记录线上版本
            if ($this->_task->action == Task::ACTION_ONLINE) {
                $this->_task->link_id = $this->_config->getReleases('release_id');
                $this->_task->ex_link_id = $dbConf->version;
            }
            $this->_task->status = Task::STATUS_DONE;
            $this->_task->save();

            // 记录当前线上版本（软链）
            $dbConf->version = $this->_config->getReleases('release_id');
            $dbConf->save();
        } catch (\Exception $e) {
            $this->_task->status = Task::STATUS_FAILED;
            $this->_task->save();
            throw $e;
        }
    }

    /**
     * 配置项目
     * @param $projectId
     * @return string
     * @throws \Exception
     */
    public function actionConfigEdit($projectId = null) {
        $conf = $projectId ? Conf::findOne($projectId) : new Conf();
        $confName = $projectId && $conf ? $conf->conf : static::getParam('conf');
        if (\Yii::$app->request->getIsPost()) {
            $conf->attributes = [
                'user_id' => \Yii::$app->user->id,
                'created_at' => time(),
                'name'   => static::getParam('name'),
                'conf'   => $confName,
                'level'  => static::getParam('level'),
            ];
            if ($conf->save()) {

                Conf::saveConfContext($confName, static::getParam('context'));
                $this->redirect('/walle/config');
            }
        }

        if ($projectId && !$conf) throw new \Exception('找不到项目配置');
        $conf->context = Conf::getConfContext($projectId ? $conf->conf : Conf::CONF_TPL);
        return $this->render('config-edit', [
            'conf' => $conf,
        ]);
    }

    /**
     * 配置项目列表
     * @return string
     */
    public function actionConfig() {
        $conf = Conf::find();

        $kw = \Yii::$app->request->post('kw');
        if ($kw) {
            $conf->where(['like', "name", $kw]);
        }
        $conf = $conf->asArray()->all();
        return $this->render('config', [
            'list' => $conf,
        ]);
    }

    /**
     * 提交任务
     * @param null $projectId
     * @return string
     */
    public function actionSubmit($projectId = null) {
        if (\Yii::$app->request->getIsPost()) {
            $task = new Task();
            $conf = Conf::findOne($projectId);
            // 只有线上才需要审核
            $status = in_array($conf->level, [Conf::LEVEL_PROD]) ? Task::STATUS_SUBMIT : Task::STATUS_PASS;
            $task->attributes = [
                'user_id' => \Yii::$app->user->id,
                'project_id' => (int)static::getParam('projectId'),
                'status' => $status,
                'action' => Task::ACTION_ONLINE,
                'created_at' => time(),
                'title' => static::getParam('title'),
                'commit_id' => static::getParam('commit'),
            ];
            if ($task->save()) {
                $this->redirect('/walle/index');
            }
        }
        if ($projectId) {
            return $this->render('submit', [
            ]);
        }
        $projects = Conf::find()->asArray()->all();
        return $this->render('select-project', [
            'projects' => $projects,
        ]);
    }

    /**
     * 获取commit历史
     * @param $projectId
     */
    public function actionGetCommitHistory($projectId) {
        $git = new Git();
        $git->setConfig(Conf::getConfigFile($projectId));
        $proj = Conf::findOne($projectId);
        if ($proj->level == Conf::LEVEL_PROD) {
            $list = $git->getTagList();
        } else {
            $list = $git->getCommitList();
        }
        $this->renderJson($list);
    }

    /**
     * 任务审核
     * @param $id
     * @param $operation
     */
    public function actionTaskOperation($id, $operation) {
        $task = Task::findOne($id);
        if (!$task) {
            static::renderJson([], -1, '任务号不存在');
        }
        $task->status = $operation == 'pass' ? Task::STATUS_PASS : Task::STATUS_REFUSE;
        $task->save();
        static::renderJson(['status' => \Yii::t('status', 'task_status_' . $task->status)]);
    }

    /**
     * 上线管理
     * @param $taskId
     * @return string
     * @throws \Exception
     */
    public function actionRollback() {
        $taskId = $this->getParam('taskId');
        $this->_task = Task::findOne($taskId);
        if (!$this->_task) {
            throw new \Exception('任务号不存在：）');
        }
        if ($this->_task->user_id != \Yii::$app->user->id) {
            throw new \Exception('不可以操作其它人的任务：）');
        }
        if ($this->_task->ex_link_id == $this->_task->link_id) {
            throw new \Exception('已回滚的任务不能再次回滚：（');
        }
        $rollbackTask = new Task();
        $conf = Conf::findOne($this->_task->project_id);
        // 只有线上才需要审核
        $status = in_array($conf->level, [Conf::LEVEL_PROD]) ? Task::STATUS_SUBMIT : Task::STATUS_PASS;
        $rollbackTask->attributes = [
            'user_id' => \Yii::$app->user->id,
            'project_id' => $this->_task->project_id,
            'status' => $status,
            'action' => Task::ACTION_ROLLBACK,
            'link_id' => $this->_task->ex_link_id,
            'ex_link_id' => $this->_task->ex_link_id,
            'created_at' => time(),
            'title' => $this->_task->title . ' - 回滚',
            'commit_id' => $this->_task->commit_id,
        ];
        if ($rollbackTask->save()) {
            $this->renderJson([
                'url' => in_array($conf->level, [Conf::LEVEL_PROD])
                    ? '/walle/index'
                    : '/walle/deploy?taskId=' . $rollbackTask->id,
            ]);
        } else {
            $this->renderJson([], -1, '生成回滚任务失败');
        }
    }

    /**
     * 上线管理
     * @param $taskId
     * @return string
     * @throws \Exception
     */
    public function actionDeploy($taskId) {
        $this->_task = Task::findOne($taskId);
        if (!$this->_task) {
            throw new \Exception('任务号不存在：）');
        }
        if ($this->_task->user_id != \Yii::$app->user->id) {
            throw new \Exception('不可以操作其它人的任务：）');
        }

        return $this->render('deploy', [
            'task' => $this->_task,
        ]);
    }


    /**
     * 获取上线进度
     * @param $taskId
     */
    public function actionGetProcess($taskId) {
        $record = Record::find()
            ->select(['action', 'status', 'memo'])
            ->where(['task_id' => $taskId,])
            ->orderBy('id desc')
            ->asArray()->one();
        $record['percent'] = isset(Record::$ACTION_PERCENT[$record['action']])
            ? Record::$ACTION_PERCENT[$record['action']]
            : 0;

        static::renderJson($record);
    }

    /**
     * 检查目录和权限
     * @return bool
     * @throws \Exception
     */
    private function _checkPermission() {
        $sync = new Sync();
        $sTime = Command::getMs();
        $sync->setConfig($this->_config);
        // 本地目录检查
        $sync->initDirector();
        // 远程目录检查
        $ret = $sync->directorAndPermission();
        // 记录执行日志
        $log = $sync->getExeLog();
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($sync, $this->_task->id, Record::ACTION_PERMSSION, $duration);
        if (!$ret) throw new \Exception('检查目录和权限出错');
        return true;
    }

    /**
     * 更新代码文件
     * @return bool
     * @throws \Exception
     */
    private function _gitUpdate() {
        // 更新代码文件
        $git = new Git();
        $sTime = Command::getMs();
        $ret = $git->setConfig($this->_config)
            ->rollback($this->_task->commit_id); // 更新到指定版本
        // 记录执行日志
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($git, $this->_task->id, Record::ACTION_CLONE, $duration);
        if (!$ret) throw new \Exception('更新代码文件出错');
        return true;
    }

    private function _preDeploy() {
        $task = new WalleTask();
        $sTime = Command::getMs();
        $task->setConfig($this->_config);
        $ret = $task->preDeploy();
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($task, $this->_task->id, Record::ACTION_CLONE, $duration);
        if (!$ret) throw new \Exception('前置操作失败');
        return true;
    }

    /**
     * 部署时触发操作
     *
     * @return bool
     * @throws \Exception
     */
    private function _postRelease() {
        $task = new WalleTask();
        $sTime = Command::getMs();
        $task->setConfig($this->_config);
        $ret = $task->postRelease();
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($task, $this->_task->id, Record::ACTION_CLONE, $duration);
        if (!$ret) throw new \Exception('前置操作失败');
        return true;
    }

    /**
     * 同步文件到服务器
     * @return bool
     * @throws \Exception
     */
    private function _rsync() {
        $sync = new Sync();
        $sync->setConfig($this->_config);
        // 同步文件
        foreach ($this->_config->getHosts() as $remoteHost) {
            $sTime = Command::getMs();
            $ret = $sync->syncFiles($remoteHost);
            // 记录执行日志
//            $log = $ret ?: $remoteHost . PHP_EOL . $sync->getLog();
            $duration = Command::getMs() - $sTime;
            $x = Record::saveRecord($sync, $this->_task->id, Record::ACTION_SYNC, $duration);
            if (!$ret) throw new \Exception('同步文件到服务器出错');
        }
        return true;
    }

    /**
     * 软链接
     */
    private function _link($version = null) {
        // 创建链接指向
        $remote = new RemoteCmd();
        $sTime = Command::getMs();
        $ret = $remote->setConfig($this->_config)->link($version);
        // 记录执行日志
        $duration = Command::getMs() - $sTime;
        $ret = Record::saveRecord($remote, $this->_task->id, Record::ACTION_LINK, $duration);
        if (!$ret) throw new \Exception($version ? '回滚失败' : '创建链接指向出错');
        return true;
    }

}
