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
        // 存在svn目录，直接update
        if (file_exists($dotSvn)) {
            $cmd[] = sprintf('cd %s ', $svnDir);
            $cmd[] = $this->_getSvnCmd('svn up -q');
            $command = join(' && ', $cmd);
            return $this->runLocalCommand($command);
        }
        // 不存在，则先checkout
        else {
            $cmd[] = sprintf('mkdir -p %s ', $svnDir);
            $cmd[] = sprintf('cd %s ', $svnDir);
            $cmd[] = $this->_getSvnCmd(sprintf('svn checkout -q %s .', $this->getConfig()->repo_url));
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
        // 兼容无trunk、无branches、无tags下为空
        $branch = ($task->branch == 'trunk' || $task->branch == '')
            ? $task->branch
            : ($this->getConfig()->repo_mode == Project::REPO_BRANCH ? 'branches/' : 'tags/') . $task->branch;
        // 先更新
        $versionSvnDir = sprintf('%s-svn', rtrim(Project::getDeployWorkspace($task->link_id), '/'));
        $cmd[] = sprintf('cd %s ', $versionSvnDir);
        $cmd[] = $this->_getSvnCmd(sprintf('svn checkout -q %s/%s .', $this->getConfig()->repo_url, $branch));
        // 更新指定文件到指定版本，并复制到同步目录
        foreach ($fileAndVersion as $assign) {
            if (in_array($assign[0], ['.', '..'])) continue;
            $cmd[] = $this->_getSvnCmd(sprintf('svn up -q %s %s', $assign[0], empty($assign[1]) ? '' : ' -r ' . $assign[1]));
            // 多层目录需要先新建父目录，否则复制失败
            if (strpos($assign[0], '/') !== false) {
                $cmd[] = sprintf('mkdir -p %s/%s',
                    Project::getDeployWorkspace($task->link_id), dirname($assign[0]));
            }
            $cmd[] = sprintf('cp -rf %s %s/%s',
                rtrim($assign[0], '/'), Project::getDeployWorkspace($task->link_id), dirname($assign[0]));
        }
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
        if ($this->getConfig()->repo_mode == Project::REPO_BRANCH) {
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

        return $list;
    }

    /**
     * 获取提交历史
     *
     * @return array
     */
    public function getCommitList($branch = 'trunk', $count = 30) {
        // 先更新
        $destination = Project::getDeployFromDir();
        $this->updateRepo($branch, $destination);
        $cmd[] = sprintf('cd %s ', static::getBranchDir($branch, $this->getConfig()->repo_mode == Project::REPO_TAG ?: false));
        $cmd[] = $this->_getSvnCmd('svn log --xml -l' . $count);
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

        return $list;
    }

    /**
     * 获取commit之间的文件
     *
     * @return array
     */
    public function getFileBetweenCommits($branch, $star, $end) {
        // 先更新
        $destination = Project::getDeployFromDir();
        $this->updateRepo($branch, $destination);
        $cmd[] = sprintf('cd %s ', static::getBranchDir($branch, $this->getConfig()->repo_mode == Project::REPO_TAG ?: false));
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
        $xml = simplexml_load_string($xmlString);
        foreach ($xml as $item) {
            $attr = $item->attributes();
            $id   = $attr->__toString();

            $history[$id] = [
                'id' => $id,
                'date' => $item->date->__toString(),
                'author' => $item->author->__toString(),
                'message' => $item->msg->__toString(),
            ];
        }
        return $history;
    }

    public static function getBranchDir($branch, $tag = false) {
        $svnDir = Project::getDeployFromDir();
        // 兼容无trunk、无branches、无tags下为空
        $branchDir = ($branch == '' || $branch == 'trunk') && !$tag
            ? $branch
            : ($tag ? 'tags/'.$branch : 'branches/'.$branch);
        return sprintf('%s/%s', $svnDir, $branchDir);
    }

    private function _getSvnCmd($cmd) {
        return sprintf("/usr/bin/env %s  --username='%s' --password='%s' --non-interactive --trust-server-cert ",
            $cmd, $this->config->repo_username, $this->config->repo_password);
    }

}
