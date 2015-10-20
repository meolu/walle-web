<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 日  8/ 2 10:43:15 2015
 *
 * @File Name: command/Git.php
 * @Description:
 * *****************************************************************/
namespace app\components;


use app\models\Project;

class Git extends Command {

    public function updateRepo($branch = 'master', $gitDir = null) {
        $gitDir = $gitDir ?: Project::getDeployFromDir();
        $dotGit = rtrim($gitDir, '/') . '/.git';
        // 存在git目录，直接pull
        if (file_exists($dotGit)) {
            $cmd[] = sprintf('cd %s ', $gitDir);
            $cmd[] = sprintf('/usr/bin/env git checkout %s', $branch);
            $cmd[] = sprintf('/usr/bin/env git fetch --all');
            $cmd[] = sprintf('/usr/bin/env git reset --hard origin/%s', $branch);
            $command = join(' && ', $cmd);
            return $this->runLocalCommand($command);
        }
        // 不存在，则先checkout
        else {
            $cmd[] = sprintf('mkdir -p %s ', $gitDir);
            $cmd[] = sprintf('cd %s ', $gitDir);
            $cmd[] = sprintf('/usr/bin/env git clone %s .', $this->getConfig()->repo_url);
            $cmd[] = sprintf('/usr/bin/env git checkout %s', $branch);
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
        // 先更新
        $destination = Project::getDeployWorkspace($task->link_id);
        $this->updateRepo($task->branch, $destination);
        $cmd[] = sprintf('cd %s ', $destination);
        $cmd[] = sprintf('/usr/bin/env git reset %s', $task->commit_id);
        $cmd[] = '/usr/bin/env git checkout .';
        $command = join(' && ', $cmd);

        return $this->runLocalCommand($command);
    }

    /**
     * 获取分支列表
     *
     * @return array
     */
    public function getBranchList() {
        $destination = Project::getDeployFromDir();
        // 先更新，其实没有必要更新
        ///$this->updateRepo('master', $destination);
        $cmd[] = sprintf('cd %s ', $destination);
        $cmd[] = '/usr/bin/env git pull';
        $cmd[] = '/usr/bin/env git branch -a';
        $command = join(' && ', $cmd);
        $result = $this->runLocalCommand($command);
        if (!$result) {
            throw new \Exception('获取分支列表失败：' . $this->getExeLog());
        }

        $history = [];
        $list = explode(PHP_EOL, $this->getExeLog());
        foreach ($list as &$item) {
            $item = trim($item);
            $remotePrefix = 'remotes/origin/';
            $remoteHeadPrefix = 'remotes/origin/HEAD';

            // 只取远端的分支，排除当前分支
            if (strcmp(substr($item, 0, strlen($remotePrefix)), $remotePrefix) === 0
                && strcmp(substr($item, 0, strlen($remoteHeadPrefix)), $remoteHeadPrefix) !== 0) {
                $item = substr($item, strlen($remotePrefix));
                $history[] = [
                    'id'      => $item,
                    'message' => $item,
                ];
            }
        }

        return $history;
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

}
