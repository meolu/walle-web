<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 一  9/21 13:48:30 2015
 *
 * @File Name: WalleController.php
 * @Description:
 * *****************************************************************/

namespace app\controllers;

use yii;
use yii\data\Pagination;
use app\components\Command;
use app\components\Folder;
use app\components\Git;
use app\components\Task as WalleTask;
use app\components\Controller;
use app\models\Task;
use app\models\Record;
use app\models\Conf;
use app\models\User;

class WalleController extends Controller {

    /**
     * 项目配置
     */
    protected $conf;

    /**
     * 上线任务配置
     */
    protected $task;

    public $enableCsrfValidation = false;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action) {
        return parent::beforeAction($action);
    }


    /**
     * 发起上线
     *
     * @throws \Exception
     */
    public function actionStartDeploy() {
        $taskId = \Yii::$app->request->post('taskId');
        if (!$taskId) {
            static::renderJson([], -1, '任务号不能为空：）');
        }
        $this->task = Task::findOne($taskId);
        if (!$this->task) {
            throw new \Exception('任务号不存在：）');
        }
        if ($this->task->user_id != \Yii::$app->user->id) {
            throw new \Exception('不可以操作其它人的任务：）');
        }
        // 任务失败或者审核通过时可发起上线
        if (!in_array($this->task->status, [Task::STATUS_PASS, Task::STATUS_FAILED])) {
            throw new \Exception('任务不能被重复执行：）');
        }

        // 配置
        $this->conf = Conf::getConf($this->task->project_id);
        // db配置
        $dbConf = Conf::findOne($this->task->project_id);

        try {
            if ($this->task->action == Task::ACTION_ONLINE) {
                $this->_makeVersion();
                $this->_checkPermission();
                $this->_gitUpdate();
                $this->_preDeploy();
                $this->_rsync();
                $this->_postRelease();
                $this->_link($this->task->link_id);
            } else {
                $this->_link($this->task->ex_link_id);
            }

            /** 至此已经发布版本到线上了，需要做一些记录工作 */

            // 记录此次上线的版本（软链号）和上线之前的版本
            ///对于回滚的任务不记录线上版本
            if ($this->task->action == Task::ACTION_ONLINE) {
                $this->task->ex_link_id = $dbConf->version;
            }
            $this->task->status = Task::STATUS_DONE;
            $this->task->save();

            // 记录当前线上版本（软链）回滚则是回滚的版本，上线为新版本
            $dbConf->version = $this->task->link_id;
            $dbConf->save();
        } catch (\Exception $e) {
            $this->task->status = Task::STATUS_FAILED;
            $this->task->save();
            throw $e;
        }
    }


    /**
     * 提交任务
     *
     * @return string
     */
    public function actionCheck() {
        $projects = Conf::find()->asArray()->all();
        return $this->render('check', [
            'projects' => $projects,
        ]);
    }


    /**
     * 获取线上文件md5
     *
     * @param $projectId
     */
    public function actionFileMd5($projectId, $file) {
        // 配置
        $this->conf = Conf::getConf($projectId);

        $cmd = new Folder();
        $cmd->setConfig($this->conf);
        $projectDir = $this->conf->release_to;
        $file = sprintf("%s/%s", rtrim($projectDir, '/'), $file);

        $cmd->getFileMd5($file);
        $log = $cmd->getExeLog();

        $this->renderJson(join("<br>", explode(PHP_EOL, $log)));
    }

    /**
     * 获取branch分支列表
     *
     * @param $projectId
     */
    public function actionGetBranch($projectId) {
        $git = new Git();
        $conf = Conf::getConf($projectId);
        $git->setConfig($conf);
        $list = $git->getBranchList();

        $this->renderJson($list);
    }

    /**
     * 获取commit历史
     *
     * @param $projectId
     */
    public function actionGetCommitHistory($projectId, $branch = 'master') {
        $git = new Git();
        $conf = Conf::getConf($projectId);
        $git->setConfig($conf);
        if ($conf->git_type == Conf::GIT_TAG) {
            $list = $git->getTagList();
        } else {
            $list = $git->getCommitList($branch);
        }
        $this->renderJson($list);
    }

    /**
     * 上线管理
     *
     * @param $taskId
     * @return string
     * @throws \Exception
     */
    public function actionDeploy($taskId) {
        $this->task = Task::findOne($taskId);
        if (!$this->task) {
            throw new \Exception('任务号不存在：）');
        }
        if ($this->task->user_id != \Yii::$app->user->id) {
            throw new \Exception('不可以操作其它人的任务：）');
        }

        return $this->render('deploy', [
            'task' => $this->task,
        ]);
    }


    /**
     * 获取上线进度
     *
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
     * 产生一个上线版本
     */
    private function _makeVersion() {
        $version = date("Ymd-His", time());
        $this->task->link_id = $version;
        return $this->task->save();
    }

    /**
     * 检查目录和权限
     *
     * @return bool
     * @throws \Exception
     */
    private function _checkPermission() {
        $folder = new Folder();
        $sTime = Command::getMs();
        $folder->setConfig($this->conf);
        // 本地宿主机目录检查
        $folder->initDirector();
        // 本地宿主机代码仓库检查

        // 远程目标目录检查，并且生成版本目录
        $ret = $folder->folderAndPermission($this->task->link_id);
        // 记录执行时间
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($folder, $this->task->id, Record::ACTION_PERMSSION, $duration);

        if (!$ret) throw new \Exception('检查目录和权限出错');
        return true;
    }

    /**
     * 更新代码文件
     *
     * @return bool
     * @throws \Exception
     */
    private function _gitUpdate() {
        // 更新代码文件
        $git = new Git();
        $sTime = Command::getMs();
        $ret = $git->setConfig($this->conf)
            ->rollback($this->task->commit_id); // 更新到指定版本
        // 记录执行时间
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($git, $this->task->id, Record::ACTION_CLONE, $duration);

        if (!$ret) throw new \Exception('更新代码文件出错');
        return true;
    }

    private function _preDeploy() {
        $task = new WalleTask();
        $sTime = Command::getMs();
        $task->setConfig($this->conf);
        $ret = $task->preDeploy();
        // 记录执行时间
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($task, $this->task->id, Record::ACTION_CLONE, $duration);

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
        $task->setConfig($this->conf);
        $ret = $task->postRelease($this->task->link_id);
        // 记录执行时间
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($task, $this->task->id, Record::ACTION_CLONE, $duration);

        if (!$ret) throw new \Exception('前置操作失败');
        return true;
    }

    /**
     * 同步文件到服务器
     *
     * @return bool
     * @throws \Exception
     */
    private function _rsync() {
        $folder = new Folder();
        $folder->setConfig($this->conf);
        // 同步文件
        foreach (Conf::getHosts() as $remoteHost) {
            $sTime = Command::getMs();
            $ret = $folder->syncFiles($remoteHost, $this->task->link_id);
            // 记录执行时间
            $duration = Command::getMs() - $sTime;
            Record::saveRecord($folder, $this->task->id, Record::ACTION_SYNC, $duration);
            if (!$ret) throw new \Exception('同步文件到服务器出错');
        }
        return true;
    }

    /**
     * 软链接
     */
    private function _link($version = null) {
        // 创建链接指向
        $folder = new Folder();
        $sTime = Command::getMs();
        $ret = $folder->setConfig($this->conf)
            ->link($version);
        // 记录执行时间
        $duration = Command::getMs() - $sTime;
        Record::saveRecord($folder, $this->task->id, Record::ACTION_LINK, $duration);

        if (!$ret) throw new \Exception($version ? '回滚失败' : '创建链接指向出错');
        return true;
    }

}
