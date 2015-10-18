<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 日  8/ 2 10:43:15 2015
 *
 * @File Name: command/Svn.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use yii\helpers\StringHelper;
use app\models\Project;

class Svn extends Command {

    public function updateRepo($branch = 'trunk', $svnDir = null) {
        $svnDir = $svnDir ?: Project::getDeployFromDir();
        $dotSvn = rtrim($svnDir, '/') . '/.svn';
        // 存在git目录，直接pull
        if (file_exists($dotSvn)) {
            $cmd[] = sprintf('cd %s ', $svnDir);
            $cmd[] = $this->_getSvnCmd('svn up');
            $command = join(' && ', $cmd);
            return $this->runLocalCommand($command);
        }
        // 不存在，则先checkout
        else {
            $cmd[] = sprintf('mkdir -p %s ', $svnDir);
            $cmd[] = sprintf('cd %s ', $svnDir);
            $cmd[] = $this->_getSvnCmd(sprintf('svn checkout %s .', $this->getConfig()->repo_url));
            $command = join(' && ', $cmd);
            return $this->runLocalCommand($command);
        }
    }

    /**
     * 更新到指定commit版本
     *
     * @param string $commit
     * @return bool
     */
    public function updateToVersion($task) {
        $copy  = GlobalHelper::str2arr($task->file_list);
        $fileAndVersion = [];
        foreach ($copy as $file) {
            $fileAndVersion[] = StringHelper::explode($file, " ", true, true);
        }
        $branch = strcmp($task->branch, 'trunk') === 0 ? $task->branch : 'branches/' . $task->branch;
        // 先更新
        $versionSvnDir = sprintf('%s-svn', rtrim(Project::getDeployWorkspace($task->link_id), '/'));
        $cmd[] = sprintf('cd %s ', $versionSvnDir);
        $cmd[] = $this->_getSvnCmd(sprintf('svn checkout %s/%s .', $this->getConfig()->repo_url, $branch));
        // 更新指定文件到指定版本，并复制到同步目录
        foreach ($fileAndVersion as $assign) {
            $cmd[] = $this->_getSvnCmd(sprintf('svn up %s %s', $assign[0], empty($assign[1]) ? '' : ' -r ' . $assign[1]));
            // 此处有可能会cp -f失败，看shell吧，到时再看要不要做兼容
            if (strpos(dirname($assign[0]), DIRECTORY_SEPARATOR) !== false) {
                $cmd[] = sprintf('mkdir -p %s/%s', Project::getDeployWorkspace($task->link_id), dirname($assign[0]));
            }
            $cmd[] = sprintf('cp -rf %s %s/%s', $assign[0], Project::getDeployWorkspace($task->link_id), $assign[0]);
        }
        $command = join(' && ', $cmd);

        return $this->runLocalCommand($command);
    }

    /**
     * 获取分支列表
     *
     * @return array
     */
    public function getBranchList($branchDir = 'branches') {
        $branchesDir = sprintf("%s/%s", rtrim(Project::getDeployFromDir(), '/'), $branchDir);
        $list[] = [
            'id' => 'trunk',
            'message' => 'trunk',
        ];
        if (!file_exists($branchesDir) && !$this->updateRepo()) {
            return $list;
        }

        $branches = new \DirectoryIterator($branchesDir);
        foreach ($branches as $branch) {
            if ($branch->isDot() || $branch->isFile()) continue;
            $list[] = [
                'id' => $branch->__toString(),
                'message' => $branch->__toString(),
            ];
        }

        return $list;
    }

    /**
     * 获取提交历史
     *
     * @return array
     */
    public function getCommitList($branch = 'master', $count = 20) {
        // 先更新
        $destination = Project::getDeployFromDir();
        $this->updateRepo($branch, $destination);
        $cmd[] = sprintf('cd %s ', $destination);
        $cmd[] = '/usr/bin/env git log -' . $count . ' --pretty="%h - %an %s" ';
        $command = join(' && ', $cmd);
        $result = $this->runLocalCommand($command);
        if (!$result) {
            throw new \Exception('获取提交历史失败：' . $this->getExeLog());
        }

        $history = [];
        // 总有一些同学没有团队协作意识，不设置好编码：(
        $log = GlobalHelper::convert2Utf8($this->getExeLog());
        $list = explode(PHP_EOL, $log);
        foreach ($list as $item) {
            $commitId = substr($item, 0, strpos($item, '-') - 1);
            $history[] = [
                'id'      => $commitId,
                'message' => $item,
            ];
        }
        return $history;
    }

    /**
     * 获取tag记录
     *
     * @return array
     */
    public function getTagList($count = 20) {
        // 先更新
        $this->updateRepo();
        $destination = Project::getDeployFromDir();
        $cmd[] = sprintf('cd %s ', $destination);
        $cmd[] = '/usr/bin/env git tag -l ';
        $command = join(' && ', $cmd);
        $result = $this->runLocalCommand($command);
        if (!$result) {
            throw new \Exception('获取tag记录失败：' . $this->getExeLog());
        }

        $history = [];
        $list = explode(PHP_EOL, $this->getExeLog());
        foreach ($list as $item) {
            $history[] = [
                'id'      => $item,
                'message' => $item,
            ];
        }
        return $history;
    }

    private function _getSvnCmd($cmd) {
        return sprintf("/usr/bin/env %s  --username %s --password %s",
            $cmd, $this->config->repo_username, $this->config->repo_password);
    }

}
