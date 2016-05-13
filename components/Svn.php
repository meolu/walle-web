<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 日  8/ 2 10:43:15 2015
 *
 * @File Name: command/Svn.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use app\models\Project;
use app\models\Task as TaskModel;
use yii\helpers\StringHelper;

class Svn extends Command {

    /**
     * 更新仓库
     *
     * @param string $branch
     * @param null $svnDir
     * @return bool|int
     */
    public function updateRepo($branch = 'trunk', $svnDir = null) {
        $svnDir = $svnDir ?: Project::getDeployFromDir();
        $dotSvn = rtrim($svnDir, '/') . '/.svn';

        if (file_exists($dotSvn)) {
            // 存在svn目录，直接update
            $cmd[] = sprintf('cd %s ', $svnDir);
            $cmd[] = $this->_getSvnCmd('svn cleanup');
            $cmd[] = $this->_getSvnCmd('svn revert . -q -R');
            $cmd[] = $this->_getSvnCmd('svn up -q --force');
            $command = join(' && ', $cmd);
            return $this->runLocalCommand($command);
        } else {
            // 不存在，则先checkout
            $cmd[] = sprintf('mkdir -p %s ', $svnDir);
            $cmd[] = sprintf('cd %s ', $svnDir);
            $cmd[] = $this->_getSvnCmd(sprintf('svn checkout -q %s .', escapeshellarg($this->getConfig()->repo_url)));
            $command = join(' && ', $cmd);
            return $this->runLocalCommand($command);
        }
    }

    /**
     * 更新到指定commit版本
     *
     * @param TaskModel $task
     * @return bool|int
     */
    public function updateToVersion(TaskModel $task) {

        // 先更新
        $versionSvnDir = rtrim(Project::getDeployWorkspace($task->link_id), '/');
        $cmd[] = sprintf('cd %s ', $versionSvnDir);
        $cmd[] = $this->_getSvnCmd(sprintf('svn up -q --force -r %d', $task->commit_id));

        $command = join(' && ', $cmd);

        return $this->runLocalCommand($command);
    }

    /**
     * 获取分支/tag列表
     * 可能后期要换成 svn ls http://xxx/branches
     *
     * @return array
     */
    public function getBranchList() {
        // 更新
        $this->updateRepo();
        $list = [];
        $branchDir = 'tags';
        // 分支模式
        if ($this->getConfig()->repo_mode == Project::REPO_MODE_BRANCH) {
            $branchDir = 'branches';
            $trunkDir  = sprintf("%s/trunk", rtrim(Project::getDeployFromDir(), '/'));

            if (file_exists($trunkDir)) {
                $list[] = [
                    'id' => 'trunk',
                    'message' => 'trunk',
                ];
            } else {
                $list[] = [
                    'id' => '',
                    'message' => \yii::t('w', 'default trunk'),
                ];
            }
        }
        $branchDir = sprintf("%s/%s", rtrim(Project::getDeployFromDir(), '/'), $branchDir);

        // 如果不存在branches目录，则跳过查找其它分支
        if (!file_exists($branchDir)) {
            return $list;
        }

        $branches = new \DirectoryIterator($branchDir);
        foreach ($branches as $branch) {
            $name = $branch->__toString();
            if ($branch->isDot() || $branch->isFile()) continue;
            if ('.svn' == $name) continue;
            $list[] = [
                'id' => $name,
                'message' => $name,
            ];
        }
        // 降序排列分支列表
        rsort($list);

        return $list;
    }

    /**
     * 获取提交历史
     *
     * @param string $branch
     * @param int $count
     * @return array
     * @throws \Exception
     */
    public function getCommitList($branch = 'trunk', $count = 30) {
        // 先更新
        $destination = Project::getDeployFromDir();
        $this->updateRepo($branch, $destination);
        $cmd[] = sprintf('cd %s ', static::getBranchDir($branch, $this->getConfig()));
        $cmd[] = $this->_getSvnCmd('svn log --xml -l ' . $count);
        $command = join(' && ', $cmd);
        $result = $this->runLocalCommand($command);
        if (!$result) {
            throw new \Exception(\yii::t('walle', 'get commit log failed') . $this->getExeLog());
        }

        // 总有一些同学没有团队协作意识，不设置好编码：(
        $log = GlobalHelper::convert2Utf8($this->getExeLog());
        return array_values(static::formatXmlLog($log));
    }

    /**
     * 获取tag记录
     *
     * @return array
     */
    public function getTagList($count = 20) {
        $branchesDir = sprintf("%s/tags", rtrim(Project::getDeployFromDir(), '/'));
        $list[] = [
            'id'      => 'trunk',
            'message' => 'trunk',
        ];
        if (!file_exists($branchesDir) && !$this->updateRepo()) {
            return $list;
        }

        $branches = new \DirectoryIterator($branchesDir);
        foreach ($branches as $branch) {
            $name = $branch->__toString();
            if ($branch->isDot() || $branch->isFile()) continue;
            if ('.svn' == $name) continue;
            $list[] = [
                'id'      => $name,
                'message' => $name,
            ];
        }
        // 降序排列分支列表
        rsort($list);

        return $list;
    }

    /**
     * 获取commit之间的文件
     *
     * @param $branch
     * @param $star
     * @param $end
     * @return array
     * @throws \Exception
     */
    public function getFileBetweenCommits($branch, $star, $end) {
        // 先更新
        $destination = Project::getDeployFromDir();
        $this->updateRepo($branch, $destination);
        $cmd[] = sprintf('cd %s ', static::getBranchDir($branch, $this->getConfig()));
        $cmd[] = $this->_getSvnCmd(sprintf('svn diff -r %d:%d --summarize', $star, $end));
        $command = join(' && ', $cmd);
        $result = $this->runLocalCommand($command);
        if (!$result) {
            throw new \Exception(\yii::t('walle', 'get commit log failed') . $this->getExeLog());
        }

        $list = [];
        $files = StringHelper::explode($this->getExeLog(), PHP_EOL);
        $files = array_map(function($item) {
            return trim(substr($item, strpos($item, " ")));
        }, $files);
        // 排除点文件
        if (in_array('.', $files)) {
            unset($files[array_search('.', $files)]);
        }
        foreach ($files as $key => $file) {
            // 如果是目录，则目录下的文件则可以不带了
            if (in_array(dirname($file), $files)) continue;
            $list[] = $file;
        }

        return $list;
    }

    /**
     * 格式化svn log xml 2 array
     *
     * @param $xmlString
     * @return array
     */
    public static function formatXmlLog($xmlString) {
        $history = [];
        $pos = strpos($xmlString, '<?xml');
        if ($pos > 0) {
            $xmlString = substr($xmlString, $pos);
        }
        $xml = simplexml_load_string($xmlString);
        foreach ($xml as $item) {
            $attr = $item->attributes();
            $id   = $attr->__toString();

            $history[$id] = [
                'id' => $id,
                'date' => $item->date->__toString(),
                'author' => $item->author->__toString(),
                'message' => htmlspecialchars($item->msg->__toString()),
            ];
        }
        return $history;
    }

    /**
     * 获取svn分支目录
     * @param $branch
     * @param Project $project
     * @return string
     */
    public static function getBranchDir($branch, Project $project) {

        $svnDir = Project::getDeployFromDir();
        if ($project->repo_mode == Project::REPO_MODE_NONTRUNK) {
            return $svnDir;
        } elseif ($branch == 'trunk') {
            return sprintf('%s/trunk', $svnDir);
        } elseif ($project->repo_mode == Project::REPO_MODE_BRANCH) {
            return sprintf('%s/branches/%s', $svnDir, $branch);
        } elseif ($project->repo_mode == Project::REPO_MODE_TAG) {
            return sprintf('%s/tags/%s', $svnDir, $branch);
        } else {
            throw new \InvalidArgumentException('error');
        }

    }

    /**
     * @param $cmd
     * @return string
     */
    private function _getSvnCmd($cmd) {
        return sprintf('/usr/bin/env LC_ALL=en_US.UTF-8 %s --username=%s --password=%s --non-interactive --trust-server-cert',
            $cmd, escapeshellarg($this->config->repo_username), escapeshellarg($this->config->repo_password));
    }

}
